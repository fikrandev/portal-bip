<?php
/**
 * Perangkat Pembelajaran Controller
 * 
 * Manages all actions for:
 * - Dashboard Perangkat Pembelajaran
 * - Kalender Pendidikan (Kaldik)
 * - Hari Efektif Sekolah (HES)
 * - Hari Efektif Belajar (HEB)
 * - Program Tahunan (Prota)
 * - Program Semester (Prosem)
 * - RPP / Modul Ajar
 * - Verification & Approval Hub
 */

require_once BASE_PATH . '/modules/kelola-perangkat-pembelajaran/models/PerangkatModel.php';

class PerangkatController
{
    private static function view(string $viewPath, array $data = [], string $pageTitle = 'Kelola Perangkat Pembelajaran', array $breadcrumbs = []): void
    {
        extract($data);
        $customSidebar = BASE_PATH . '/modules/kelola-perangkat-pembelajaran/views/sidebar.php';
        
        ob_start();
        include BASE_PATH . '/modules/kelola-perangkat-pembelajaran/views/' . $viewPath . '.php';
        $content = ob_get_clean();
        
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    private static function getCommonFilters(): array
    {
        $taList = PerangkatModel::getTahunAkademikList();
        $activeTa = null;
        foreach ($taList as $ta) {
            if ($ta['is_active']) { $activeTa = $ta['id']; break; }
        }
        if (!$activeTa && !empty($taList)) {
            $activeTa = $taList[0]['id'];
        }

        $filterTa = isset($_GET['ta']) && $_GET['ta'] !== '' ? (int) $_GET['ta'] : $activeTa;
        $filterSemester = isset($_GET['semester']) && $_GET['semester'] !== '' ? trim($_GET['semester']) : '';
        $filterStatus = isset($_GET['status']) && $_GET['status'] !== '' ? trim($_GET['status']) : '';
        $filterGuru = isset($_GET['guru_id']) && $_GET['guru_id'] !== '' ? (int) $_GET['guru_id'] : null;
        $filterUnit = isset($_GET['unit']) && $_GET['unit'] !== '' ? trim($_GET['unit']) : '';
        $search = trim($_GET['search'] ?? '');

        return [
            'ta_list' => $taList,
            'active_ta' => $activeTa,
            'filter_ta' => $filterTa,
            'filter_semester' => $filterSemester,
            'filter_status' => $filterStatus,
            'filter_guru' => $filterGuru,
            'filter_unit' => $filterUnit,
            'unit_list' => PerangkatModel::getUnitList(),
            'search' => $search,
            'guru_list' => PerangkatModel::getGuruList(),
            'logged_in_guru' => PerangkatModel::getLoggedInGuru(),
            'kelas_list' => PerangkatModel::getKelasList($filterTa),
            'all_kaldiks' => PerangkatModel::getKaldikListByUnit(null, $filterTa, $filterSemester)
        ];
    }



    // ── DASHBOARD ──────────────────────────────────────────
    public static function index(): void
    {
        self::dashboard();
    }

    public static function dashboard(): void
    {
        $filters = self::getCommonFilters();
        $stats = PerangkatModel::getDashboardStats($filters['filter_ta'], $filters['filter_semester'], $filters['filter_unit']);
        
        // Fetch recent submissions (10 items)
        $recentItems = PerangkatModel::getAll([
            'tahun_akademik_id' => $filters['filter_ta'],
            'semester' => $filters['filter_semester'],
            'unit' => $filters['filter_unit']
        ], 10, 0);

        // Pending verifications
        $pendingItems = PerangkatModel::getAll([
            'status' => 'diajukan',
            'tahun_akademik_id' => $filters['filter_ta'],
            'unit' => $filters['filter_unit']
        ], 5, 0);

        self::view('dashboard/index', array_merge($filters, [
            'stats' => $stats,
            'recent_items' => $recentItems,
            'pending_items' => $pendingItems,
            'can_approve' => PerangkatModel::canApprove()
        ]), 'Dashboard Perangkat Pembelajaran', [
            ['label' => 'Perangkat Pembelajaran', 'url' => url('kelola-perangkat-pembelajaran')],
            ['label' => 'Dashboard']
        ]);
    }

    // ── 1. KALENDER PENDIDIKAN (KALDIK) ─────────────────────
    public static function kaldik(): void
    {
        $filters = self::getCommonFilters();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;
        $filterIsActive = $_GET['is_active'] ?? '';

        $filterArgs = [
            'tipe' => 'kaldik',
            'unit' => $filters['filter_unit'],
            'is_active' => $filterIsActive,
            'tahun_akademik_id' => $filters['filter_ta'],
            'semester' => $filters['filter_semester'],
            'status' => $filters['filter_status'],
            'search' => $filters['search']
        ];

        $items = PerangkatModel::getAll($filterArgs, $limit, $offset);
        $total = PerangkatModel::countAll($filterArgs);
        $totalPages = max(1, ceil($total / $limit));

        // Grouped by unit for overview
        $kaldikByUnit = [];
        foreach ($filters['unit_list'] as $uKey => $uInfo) {
            $kaldikByUnit[$uKey] = PerangkatModel::getAll([
                'tipe' => 'kaldik',
                'unit' => $uKey,
                'tahun_akademik_id' => $filters['filter_ta'],
                'semester' => $filters['filter_semester']
            ], 10, 0);
        }

        self::view('kaldik/index', array_merge($filters, [
            'items' => $items,
            'kaldik_by_unit' => $kaldikByUnit,
            'filter_is_active' => $filterIsActive,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'can_approve' => PerangkatModel::canApprove()
        ]), 'Kalender Pendidikan (Kaldik)', [
            ['label' => 'Perangkat Pembelajaran', 'url' => url('kelola-perangkat-pembelajaran')],
            ['label' => 'Kalender Pendidikan']
        ]);
    }

    public static function createKaldik(): void
    {
        $filters = self::getCommonFilters();
        self::view('kaldik/create', $filters, 'Buat Kalender Pendidikan', [
            ['label' => 'Perangkat Pembelajaran', 'url' => url('kelola-perangkat-pembelajaran')],
            ['label' => 'Kalender Pendidikan', 'url' => url('kelola-perangkat-pembelajaran/kaldik')],
            ['label' => 'Buat Baru']
        ]);
    }

    public static function storeKaldik(): void
    {
        if (!CSRF::validate()) { Response::withError(url('kelola-perangkat-pembelajaran/kaldik'), 'Token CSRF tidak valid.'); return; }
        
        $validator = Validator::make($_POST)
            ->required('judul', 'Judul Kaldik')
            ->required('unit', 'Unit Sekolah')
            ->required('tahun_akademik_id', 'Tahun Ajaran');
            
        if ($validator->fails()) { Response::backWithErrors($validator->errors(), $_POST); return; }

        // Process dynamic agenda items
        $agendas = [];
        if (!empty($_POST['agenda_kegiatan'])) {
            foreach ($_POST['agenda_kegiatan'] as $idx => $kegiatan) {
                if (!empty($kegiatan)) {
                    $agendas[] = [
                        'tanggal_mulai' => $_POST['agenda_tgl_mulai'][$idx] ?? '',
                        'tanggal_selesai' => $_POST['agenda_tgl_selesai'][$idx] ?? '',
                        'kegiatan' => $kegiatan,
                        'kategori' => $_POST['agenda_kategori'][$idx] ?? 'kbm',
                        'semester' => $_POST['agenda_semester'][$idx] ?? 'Ganjil',
                        'keterangan' => $_POST['agenda_keterangan'][$idx] ?? '',
                        'pengecualian_tingkat' => $_POST['agenda_pengecualian'][$idx] ?? ''
                    ];
                }
            }
        }

        $filePath = null;
        $unit = $_POST['unit'] ?? 'SD';
        $taId = (int) $_POST['tahun_akademik_id'];
        $isActive = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

        $data = [
            'tipe' => 'kaldik',
            'unit' => $unit,
            'is_active' => $isActive,
            'judul' => trim($_POST['judul']),
            'tahun_akademik_id' => $taId,
            'semester' => $_POST['semester'] ?? 'Ganjil',
            'guru_id' => !empty($_POST['guru_id']) ? (int)$_POST['guru_id'] : null,
            'guru_nama' => trim($_POST['guru_nama'] ?? Auth::name() ?? 'Administrator'),
            'guru_nip' => trim($_POST['guru_nip'] ?? ''),
            'konten_json' => [
                'deskripsi' => trim($_POST['deskripsi'] ?? ''),
                'catatan' => trim($_POST['catatan'] ?? ''),
                'agendas' => $agendas
            ],
            'file_lampiran' => $filePath,
            'status' => 'disetujui',
            'created_by' => Auth::id() ?? 1
        ];

        $id = PerangkatModel::create($data);

        // If set as active, make it active for this unit
        if ($isActive === 1) {
            PerangkatModel::setActiveOnly($id, $unit, $taId);
        }

        Response::withSuccess(url('kelola-perangkat-pembelajaran/kaldik'), "Grup Kalender Pendidikan '{$data['judul']}' (Unit {$unit}) berhasil disimpan.");
    }

    public static function editKaldik(int $id): void
    {
        $item = PerangkatModel::getById($id);
        if (!$item || $item['tipe'] !== 'kaldik') {
            Response::withError(url('kelola-perangkat-pembelajaran/kaldik'), 'Data Kaldik tidak ditemukan.');
            return;
        }

        $filters = self::getCommonFilters();
        $konten = !empty($item['konten_json']) ? json_decode($item['konten_json'], true) : [];
        $logs = PerangkatModel::getLogs($id);

        self::view('kaldik/edit', array_merge($filters, [
            'item' => $item,
            'konten' => $konten,
            'logs' => $logs
        ]), 'Edit Kalender Pendidikan', [
            ['label' => 'Perangkat Pembelajaran', 'url' => url('kelola-perangkat-pembelajaran')],
            ['label' => 'Kalender Pendidikan', 'url' => url('kelola-perangkat-pembelajaran/kaldik')],
            ['label' => 'Edit']
        ]);
    }

    public static function updateKaldik(int $id): void
    {
        if (!CSRF::validate()) { Response::withError(url('kelola-perangkat-pembelajaran/kaldik'), 'Token CSRF tidak valid.'); return; }
        
        $item = PerangkatModel::getById($id);
        if (!$item || $item['tipe'] !== 'kaldik') {
            Response::withError(url('kelola-perangkat-pembelajaran/kaldik'), 'Data Kaldik tidak ditemukan.');
            return;
        }

        $validator = Validator::make($_POST)
            ->required('judul', 'Judul Kaldik')
            ->required('tahun_akademik_id', 'Tahun Ajaran');
            
        if ($validator->fails()) { Response::backWithErrors($validator->errors(), $_POST); return; }

        $agendas = [];
        if (!empty($_POST['agenda_kegiatan'])) {
            foreach ($_POST['agenda_kegiatan'] as $idx => $kegiatan) {
                if (!empty($kegiatan)) {
                    $agendas[] = [
                        'tanggal_mulai' => $_POST['agenda_tgl_mulai'][$idx] ?? '',
                        'tanggal_selesai' => $_POST['agenda_tgl_selesai'][$idx] ?? '',
                        'kegiatan' => $kegiatan,
                        'kategori' => $_POST['agenda_kategori'][$idx] ?? 'kbm',
                        'semester' => $_POST['agenda_semester'][$idx] ?? 'Ganjil',
                        'keterangan' => $_POST['agenda_keterangan'][$idx] ?? '',
                        'pengecualian_tingkat' => $_POST['agenda_pengecualian'][$idx] ?? ''
                    ];
                }
            }
        }

        $filePath = null;

        $unit = $_POST['unit'] ?? $item['unit'] ?? 'SD';
        $taId = (int) $_POST['tahun_akademik_id'];
        $isActive = isset($_POST['is_active']) ? (int)$_POST['is_active'] : (int)($item['is_active'] ?? 1);

        $data = [
            'unit' => $unit,
            'is_active' => $isActive,
            'judul' => trim($_POST['judul']),
            'tahun_akademik_id' => $taId,
            'semester' => $_POST['semester'] ?? $item['semester'] ?? 'Ganjil',
            'guru_id' => !empty($_POST['guru_id']) ? (int)$_POST['guru_id'] : null,
            'guru_nama' => trim($_POST['guru_nama'] ?? Auth::name() ?? 'Administrator'),
            'guru_nip' => trim($_POST['guru_nip'] ?? ''),
            'konten_json' => [
                'deskripsi' => trim($_POST['deskripsi'] ?? ''),
                'catatan' => trim($_POST['catatan'] ?? ''),
                'agendas' => $agendas
            ],
            'file_lampiran' => $filePath,
            'status' => 'disetujui'
        ];

        PerangkatModel::update($id, $data);

        if ($isActive === 1) {
            PerangkatModel::setActiveOnly($id, $unit, $taId);
        }

        Response::withSuccess(url('kelola-perangkat-pembelajaran/kaldik'), 'Grup Kalender Pendidikan berhasil diperbarui.');
    }

    public static function toggleActiveKaldik(int $id): void
    {
        if (!CSRF::validate()) { 
            Response::withError(url('kelola-perangkat-pembelajaran/kaldik'), 'Token CSRF tidak valid.'); 
            return; 
        }
        $item = PerangkatModel::getById($id);
        if (!$item || $item['tipe'] !== 'kaldik') {
            Response::withError(url('kelola-perangkat-pembelajaran/kaldik'), 'Grup Kaldik tidak ditemukan.');
            return;
        }

        $newActive = PerangkatModel::toggleActive($id);
        $statusLabel = $newActive ? 'AKTIF (Acuan Utama)' : 'NON-AKTIF (Arsip)';
        Response::withSuccess(url('kelola-perangkat-pembelajaran/kaldik'), "Status Grup Kaldik '{$item['judul']}' (Unit {$item['unit']}) berhasil diubah menjadi {$statusLabel}.");
    }

    public static function addKaldikAgenda(int $id): void
    {
        if (!CSRF::validate()) { 
            Response::withError(url("kelola-perangkat-pembelajaran/kaldik/detail/{$id}"), 'Token CSRF tidak valid.'); 
            return; 
        }
        $item = PerangkatModel::getById($id);
        if (!$item || $item['tipe'] !== 'kaldik') {
            Response::withError(url('kelola-perangkat-pembelajaran/kaldik'), 'Grup Kaldik tidak ditemukan.');
            return;
        }

        $validator = Validator::make($_POST)
            ->required('kegiatan', 'Nama Kegiatan')
            ->required('tanggal_mulai', 'Tanggal Mulai');

        if ($validator->fails()) {
            Response::backWithErrors($validator->errors(), $_POST);
            return;
        }

        PerangkatModel::addAgenda($id, [
            'tanggal_mulai' => $_POST['tanggal_mulai'],
            'tanggal_selesai' => !empty($_POST['tanggal_selesai']) ? $_POST['tanggal_selesai'] : $_POST['tanggal_mulai'],
            'kegiatan' => trim($_POST['kegiatan']),
            'kategori' => $_POST['kategori'] ?? 'kbm',
            'semester' => $_POST['semester'] ?? 'Ganjil',
            'keterangan' => trim($_POST['keterangan'] ?? ''),
            'pengecualian_tingkat' => trim($_POST['pengecualian_tingkat'] ?? '')
        ]);

        Response::withSuccess(url("kelola-perangkat-pembelajaran/kaldik/detail/{$id}"), 'Agenda kegiatan berhasil ditambahkan ke dalam Kalender Pendidikan.');
    }

    public static function deleteKaldikAgenda(int $id): void
    {
        if (!CSRF::validate()) { 
            Response::withError(url("kelola-perangkat-pembelajaran/kaldik/detail/{$id}"), 'Token CSRF tidak valid.'); 
            return; 
        }
        $index = isset($_POST['agenda_index']) ? (int)$_POST['agenda_index'] : -1;
        if ($index >= 0) {
            PerangkatModel::deleteAgenda($id, $index);
            Response::withSuccess(url("kelola-perangkat-pembelajaran/kaldik/detail/{$id}"), 'Agenda kegiatan berhasil dihapus.');
        } else {
            Response::withError(url("kelola-perangkat-pembelajaran/kaldik/detail/{$id}"), 'Indeks agenda tidak valid.');
        }
    }

    public static function detailKaldik(int $id): void
    {
        $item = PerangkatModel::getById($id);
        if (!$item) { Response::withError(url('kelola-perangkat-pembelajaran/kaldik'), 'Data tidak ditemukan.'); return; }

        $konten = !empty($item['konten_json']) ? json_decode($item['konten_json'], true) : [];
        $logs = PerangkatModel::getLogs($id);

        self::view('kaldik/detail', [
            'item' => $item,
            'konten' => $konten,
            'logs' => $logs,
            'can_approve' => PerangkatModel::canApprove()
        ], 'Detail Kalender Pendidikan', [
            ['label' => 'Perangkat Pembelajaran', 'url' => url('kelola-perangkat-pembelajaran')],
            ['label' => 'Kalender Pendidikan', 'url' => url('kelola-perangkat-pembelajaran/kaldik')],
            ['label' => 'Detail']
        ]);
    }

    public static function cetakKaldik(int $id): void
    {
        $item = PerangkatModel::getById($id);
        if (!$item) { Response::withError(url('kelola-perangkat-pembelajaran/kaldik'), 'Data tidak ditemukan.'); return; }
        
        $konten = !empty($item['konten_json']) ? json_decode($item['konten_json'], true) : [];
        include BASE_PATH . '/modules/kelola-perangkat-pembelajaran/views/kaldik/cetak.php';
        exit;
    }

    // ── 2. HARI EFEKTIF SEKOLAH (HES) ───────────────────────
    public static function hes(): void
    {
        $filters = self::getCommonFilters();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $filterArgs = [
            'tipe' => 'hes',
            'unit' => $filters['filter_unit'],
            'tahun_akademik_id' => $filters['filter_ta'],
            'semester' => $filters['filter_semester'],
            'status' => $filters['filter_status'],
            'search' => $filters['search']
        ];

        $items = PerangkatModel::getAll($filterArgs, $limit, $offset);
        $total = PerangkatModel::countAll($filterArgs);
        $totalPages = max(1, ceil($total / $limit));

        self::view('hes/index', array_merge($filters, [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'can_approve' => PerangkatModel::canApprove()
        ]), 'Hari Efektif Sekolah (HES)', [
            ['label' => 'Perangkat Pembelajaran', 'url' => url('kelola-perangkat-pembelajaran')],
            ['label' => 'Hari Efektif Sekolah (HES)']
        ]);
    }

    public static function createHes(): void
    {
        $filters = self::getCommonFilters();
        self::view('hes/create', $filters, 'Buat Hari Efektif Sekolah (HES)', [
            ['label' => 'Perangkat Pembelajaran', 'url' => url('kelola-perangkat-pembelajaran')],
            ['label' => 'HES', 'url' => url('kelola-perangkat-pembelajaran/hes')],
            ['label' => 'Buat Baru']
        ]);
    }

    public static function storeHes(): void
    {
        if (!CSRF::validate()) { Response::withError(url('kelola-perangkat-pembelajaran/hes'), 'Token CSRF tidak valid.'); return; }

        $validator = Validator::make($_POST)
            ->required('judul', 'Judul Dokumen')
            ->required('unit', 'Unit Sekolah')
            ->required('tahun_akademik_id', 'Tahun Ajaran')
            ->required('semester', 'Semester');
            
        if ($validator->fails()) { Response::backWithErrors($validator->errors(), $_POST); return; }

        // Process monthly HES rows
        $bulanRows = [];
        $totalHariKalender = 0;
        $totalHariLibur = 0;
        $totalHariEfektif = 0;

        if (!empty($_POST['bulan_nama'])) {
            foreach ($_POST['bulan_nama'] as $idx => $namaBulan) {
                $hk = (int) ($_POST['bulan_hk'][$idx] ?? 0);
                $hl = (int) ($_POST['bulan_hl'][$idx] ?? 0);
                $he = (int) ($_POST['bulan_he'][$idx] ?? max(0, $hk - $hl));
                $ket = trim($_POST['bulan_ket'][$idx] ?? '');

                $bulanRows[] = [
                    'bulan' => $namaBulan,
                    'hari_kalender' => $hk,
                    'hari_libur' => $hl,
                    'hari_efektif' => $he,
                    'keterangan' => $ket
                ];

                $totalHariKalender += $hk;
                $totalHariLibur += $hl;
                $totalHariEfektif += $he;
            }
        }

        $filePath = null;
        if (!empty($_FILES['file_lampiran']['name'])) {
            $filePath = PerangkatModel::uploadFile($_FILES['file_lampiran'], 'hes');
        }

        $data = [
            'tipe' => 'hes',
            'unit' => $_POST['unit'] ?? 'SD',
            'kaldik_id' => !empty($_POST['kaldik_id']) ? (int)$_POST['kaldik_id'] : null,
            'judul' => trim($_POST['judul']),
            'tahun_akademik_id' => (int) $_POST['tahun_akademik_id'],
            'semester' => $_POST['semester'],
            'guru_id' => !empty($_POST['guru_id']) ? (int)$_POST['guru_id'] : null,
            'guru_nama' => trim($_POST['guru_nama'] ?? Auth::name() ?? 'Administrator'),
            'guru_nip' => trim($_POST['guru_nip'] ?? ''),
            'konten_json' => [
                'deskripsi' => trim($_POST['deskripsi'] ?? ''),
                'bulan_rows' => $bulanRows,
                'total_hk' => $totalHariKalender,
                'total_hl' => $totalHariLibur,
                'total_he' => $totalHariEfektif
            ],
            'file_lampiran' => $filePath,
            'status' => isset($_POST['ajukan']) ? 'diajukan' : 'draft',
            'created_by' => Auth::id() ?? 1
        ];

        $id = PerangkatModel::create($data);
        if (isset($_POST['ajukan'])) {
            PerangkatModel::updateStatus($id, 'diajukan', 'Pengajuan awal saat pembuatan.', Auth::id(), Auth::name());
        }

        Response::withSuccess(url('kelola-perangkat-pembelajaran/hes'), 'Data Hari Efektif Sekolah (HES) berhasil disimpan.');
    }

    public static function editHes(int $id): void
    {
        $item = PerangkatModel::getById($id);
        if (!$item || $item['tipe'] !== 'hes') {
            Response::withError(url('kelola-perangkat-pembelajaran/hes'), 'Data HES tidak ditemukan.');
            return;
        }

        $filters = self::getCommonFilters();
        $konten = !empty($item['konten_json']) ? json_decode($item['konten_json'], true) : [];
        $logs = PerangkatModel::getLogs($id);

        self::view('hes/edit', array_merge($filters, [
            'item' => $item,
            'konten' => $konten,
            'logs' => $logs
        ]), 'Edit Hari Efektif Sekolah (HES)', [
            ['label' => 'Perangkat Pembelajaran', 'url' => url('kelola-perangkat-pembelajaran')],
            ['label' => 'HES', 'url' => url('kelola-perangkat-pembelajaran/hes')],
            ['label' => 'Edit']
        ]);
    }

    public static function updateHes(int $id): void
    {
        if (!CSRF::validate()) { Response::withError(url('kelola-perangkat-pembelajaran/hes'), 'Token CSRF tidak valid.'); return; }

        $item = PerangkatModel::getById($id);
        if (!$item || $item['tipe'] !== 'hes') {
            Response::withError(url('kelola-perangkat-pembelajaran/hes'), 'Data HES tidak ditemukan.');
            return;
        }

        $validator = Validator::make($_POST)
            ->required('judul', 'Judul Dokumen')
            ->required('unit', 'Unit Sekolah')
            ->required('tahun_akademik_id', 'Tahun Ajaran')
            ->required('semester', 'Semester');
            
        if ($validator->fails()) { Response::backWithErrors($validator->errors(), $_POST); return; }

        $bulanRows = [];
        $totalHariKalender = 0;
        $totalHariLibur = 0;
        $totalHariEfektif = 0;

        if (!empty($_POST['bulan_nama'])) {
            foreach ($_POST['bulan_nama'] as $idx => $namaBulan) {
                $hk = (int) ($_POST['bulan_hk'][$idx] ?? 0);
                $hl = (int) ($_POST['bulan_hl'][$idx] ?? 0);
                $he = (int) ($_POST['bulan_he'][$idx] ?? max(0, $hk - $hl));
                $ket = trim($_POST['bulan_ket'][$idx] ?? '');

                $bulanRows[] = [
                    'bulan' => $namaBulan,
                    'hari_kalender' => $hk,
                    'hari_libur' => $hl,
                    'hari_efektif' => $he,
                    'keterangan' => $ket
                ];

                $totalHariKalender += $hk;
                $totalHariLibur += $hl;
                $totalHariEfektif += $he;
            }
        }

        $filePath = $item['file_lampiran'];
        if (!empty($_FILES['file_lampiran']['name'])) {
            $newPath = PerangkatModel::uploadFile($_FILES['file_lampiran'], 'hes');
            if ($newPath) { $filePath = $newPath; }
        }

        $status = $item['status'];
        if (isset($_POST['ajukan'])) {
            $status = 'diajukan';
        }

        $data = [
            'unit' => $_POST['unit'] ?? $item['unit'] ?? 'SD',
            'kaldik_id' => !empty($_POST['kaldik_id']) ? (int)$_POST['kaldik_id'] : null,
            'judul' => trim($_POST['judul']),
            'tahun_akademik_id' => (int) $_POST['tahun_akademik_id'],
            'semester' => $_POST['semester'],
            'guru_id' => !empty($_POST['guru_id']) ? (int)$_POST['guru_id'] : null,
            'guru_nama' => trim($_POST['guru_nama'] ?? Auth::name() ?? 'Administrator'),
            'guru_nip' => trim($_POST['guru_nip'] ?? ''),
            'konten_json' => [
                'deskripsi' => trim($_POST['deskripsi'] ?? ''),
                'bulan_rows' => $bulanRows,
                'total_hk' => $totalHariKalender,
                'total_hl' => $totalHariLibur,
                'total_he' => $totalHariEfektif
            ],
            'file_lampiran' => $filePath,
            'status' => $status
        ];

        PerangkatModel::update($id, $data);

        if (isset($_POST['ajukan']) && $item['status'] !== 'diajukan') {
            PerangkatModel::updateStatus($id, 'diajukan', 'Diajukan untuk peninjauan/verifikasi.', Auth::id(), Auth::name());
        }

        Response::withSuccess(url('kelola-perangkat-pembelajaran/hes'), 'Data Hari Efektif Sekolah (HES) berhasil diperbarui.');
    }

    public static function detailHes(int $id): void
    {
        $item = PerangkatModel::getById($id);
        if (!$item) { Response::withError(url('kelola-perangkat-pembelajaran/hes'), 'Data tidak ditemukan.'); return; }

        $konten = !empty($item['konten_json']) ? json_decode($item['konten_json'], true) : [];
        $logs = PerangkatModel::getLogs($id);

        self::view('hes/detail', [
            'item' => $item,
            'konten' => $konten,
            'logs' => $logs,
            'can_approve' => PerangkatModel::canApprove()
        ], 'Detail Hari Efektif Sekolah (HES)', [
            ['label' => 'Perangkat Pembelajaran', 'url' => url('kelola-perangkat-pembelajaran')],
            ['label' => 'HES', 'url' => url('kelola-perangkat-pembelajaran/hes')],
            ['label' => 'Detail']
        ]);
    }

    public static function cetakHes(int $id): void
    {
        $item = PerangkatModel::getById($id);
        if (!$item) { Response::withError(url('kelola-perangkat-pembelajaran/hes'), 'Data tidak ditemukan.'); return; }
        
        $konten = !empty($item['konten_json']) ? json_decode($item['konten_json'], true) : [];
        include BASE_PATH . '/modules/kelola-perangkat-pembelajaran/views/hes/cetak.php';
        exit;
    }

    // ── 3. HARI EFEKTIF BELAJAR (HEB) ───────────────────────
    public static function heb(): void
    {
        $filters = self::getCommonFilters();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $filterArgs = [
            'tipe' => 'heb',
            'unit' => $filters['filter_unit'],
            'tahun_akademik_id' => $filters['filter_ta'],
            'semester' => $filters['filter_semester'],
            'status' => $filters['filter_status'],
            'search' => $filters['search']
        ];

        $items = PerangkatModel::getAll($filterArgs, $limit, $offset);
        $total = PerangkatModel::countAll($filterArgs);
        $totalPages = max(1, ceil($total / $limit));

        self::view('heb/index', array_merge($filters, [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'can_approve' => PerangkatModel::canApprove()
        ]), 'Hari Efektif Belajar (HEB)', [
            ['label' => 'Perangkat Pembelajaran', 'url' => url('kelola-perangkat-pembelajaran')],
            ['label' => 'Hari Efektif Belajar (HEB)']
        ]);
    }

    public static function createHeb(): void
    {
        $filters = self::getCommonFilters();
        self::view('heb/create', $filters, 'Buat Hari Efektif Belajar (HEB)', [
            ['label' => 'Perangkat Pembelajaran', 'url' => url('kelola-perangkat-pembelajaran')],
            ['label' => 'HEB', 'url' => url('kelola-perangkat-pembelajaran/heb')],
            ['label' => 'Buat Baru']
        ]);
    }

    public static function storeHeb(): void
    {
        if (!CSRF::validate()) { Response::withError(url('kelola-perangkat-pembelajaran/heb'), 'Token CSRF tidak valid.'); return; }

        $validator = Validator::make($_POST)
            ->required('judul', 'Judul Dokumen')
            ->required('unit', 'Unit Sekolah')
            ->required('tahun_akademik_id', 'Tahun Ajaran')
            ->required('semester', 'Semester')
            ->required('mata_pelajaran', 'Mata Pelajaran')
            ->required('tingkat_kelas', 'Tingkat / Kelas');
            
        if ($validator->fails()) { Response::backWithErrors($validator->errors(), $_POST); return; }

        $jpPerMinggu = (int) ($_POST['jp_per_minggu'] ?? 2);
        
        $pekanRows = [];
        $totalPekanSemua = 0;
        $totalPekanTidakEfektif = 0;
        $totalPekanEfektif = 0;

        if (!empty($_POST['bulan_nama'])) {
            foreach ($_POST['bulan_nama'] as $idx => $namaBulan) {
                $jmlPekan = (int) ($_POST['pekan_total'][$idx] ?? 0);
                $pekanTdkEfektif = (int) ($_POST['pekan_non_efektif'][$idx] ?? 0);
                $pekanEfektif = (int) ($_POST['pekan_efektif'][$idx] ?? max(0, $jmlPekan - $pekanTdkEfektif));
                $ket = trim($_POST['pekan_ket'][$idx] ?? '');

                $pekanRows[] = [
                    'bulan' => $namaBulan,
                    'pekan_total' => $jmlPekan,
                    'pekan_non_efektif' => $pekanTdkEfektif,
                    'pekan_efektif' => $pekanEfektif,
                    'keterangan' => $ket
                ];

                $totalPekanSemua += $jmlPekan;
                $totalPekanTidakEfektif += $pekanTdkEfektif;
                $totalPekanEfektif += $pekanEfektif;
            }
        }

        $totalJpEfektif = $totalPekanEfektif * $jpPerMinggu;

        $filePath = null;
        if (!empty($_FILES['file_lampiran']['name'])) {
            $filePath = PerangkatModel::uploadFile($_FILES['file_lampiran'], 'heb');
        }

        $data = [
            'tipe' => 'heb',
            'unit' => $_POST['unit'] ?? 'SD',
            'kaldik_id' => !empty($_POST['kaldik_id']) ? (int)$_POST['kaldik_id'] : null,
            'judul' => trim($_POST['judul']),
            'tahun_akademik_id' => (int) $_POST['tahun_akademik_id'],
            'semester' => $_POST['semester'],
            'mata_pelajaran' => trim($_POST['mata_pelajaran']),
            'tingkat_kelas' => trim($_POST['tingkat_kelas']),
            'fase' => trim($_POST['fase'] ?? ''),
            'alokasi_waktu' => "{$totalJpEfektif} JP ({$jpPerMinggu} JP/Minggu)",
            'guru_id' => !empty($_POST['guru_id']) ? (int)$_POST['guru_id'] : null,
            'guru_nama' => trim($_POST['guru_nama'] ?? Auth::name() ?? 'Administrator'),
            'guru_nip' => trim($_POST['guru_nip'] ?? ''),
            'konten_json' => [
                'jp_per_minggu' => $jpPerMinggu,
                'pekan_rows' => $pekanRows,
                'total_pekan_semua' => $totalPekanSemua,
                'total_pekan_tidak_efektif' => $totalPekanTidakEfektif,
                'total_pekan_efektif' => $totalPekanEfektif,
                'total_jp_efektif' => $totalJpEfektif,
                'distribusi_jp' => [
                    'jp_kbm' => (int) ($_POST['jp_kbm'] ?? round($totalJpEfektif * 0.85)),
                    'jp_penilaian' => (int) ($_POST['jp_penilaian'] ?? round($totalJpEfektif * 0.10)),
                    'jp_cadangan' => (int) ($_POST['jp_cadangan'] ?? max(0, $totalJpEfektif - round($totalJpEfektif * 0.95)))
                ]
            ],
            'file_lampiran' => $filePath,
            'status' => isset($_POST['ajukan']) ? 'diajukan' : 'draft',
            'created_by' => Auth::id() ?? 1
        ];

        $id = PerangkatModel::create($data);
        if (isset($_POST['ajukan'])) {
            PerangkatModel::updateStatus($id, 'diajukan', 'Pengajuan awal saat pembuatan.', Auth::id(), Auth::name());
        }

        Response::withSuccess(url('kelola-perangkat-pembelajaran/heb'), 'Data Hari Efektif Belajar (HEB) berhasil disimpan.');
    }

    public static function editHeb(int $id): void
    {
        $item = PerangkatModel::getById($id);
        if (!$item || $item['tipe'] !== 'heb') {
            Response::withError(url('kelola-perangkat-pembelajaran/heb'), 'Data HEB tidak ditemukan.');
            return;
        }

        $filters = self::getCommonFilters();
        $konten = !empty($item['konten_json']) ? json_decode($item['konten_json'], true) : [];
        $logs = PerangkatModel::getLogs($id);

        self::view('heb/edit', array_merge($filters, [
            'item' => $item,
            'konten' => $konten,
            'logs' => $logs
        ]), 'Edit Hari Efektif Belajar (HEB)', [
            ['label' => 'Perangkat Pembelajaran', 'url' => url('kelola-perangkat-pembelajaran')],
            ['label' => 'HEB', 'url' => url('kelola-perangkat-pembelajaran/heb')],
            ['label' => 'Edit']
        ]);
    }

    public static function updateHeb(int $id): void
    {
        if (!CSRF::validate()) { Response::withError(url('kelola-perangkat-pembelajaran/heb'), 'Token CSRF tidak valid.'); return; }

        $item = PerangkatModel::getById($id);
        if (!$item || $item['tipe'] !== 'heb') {
            Response::withError(url('kelola-perangkat-pembelajaran/heb'), 'Data HEB tidak ditemukan.');
            return;
        }

        $validator = Validator::make($_POST)
            ->required('judul', 'Judul Dokumen')
            ->required('unit', 'Unit Sekolah')
            ->required('tahun_akademik_id', 'Tahun Ajaran')
            ->required('semester', 'Semester')
            ->required('mata_pelajaran', 'Mata Pelajaran')
            ->required('tingkat_kelas', 'Tingkat / Kelas');
            
        if ($validator->fails()) { Response::backWithErrors($validator->errors(), $_POST); return; }

        $jpPerMinggu = (int) ($_POST['jp_per_minggu'] ?? 2);
        
        $pekanRows = [];
        $totalPekanSemua = 0;
        $totalPekanTidakEfektif = 0;
        $totalPekanEfektif = 0;

        if (!empty($_POST['bulan_nama'])) {
            foreach ($_POST['bulan_nama'] as $idx => $namaBulan) {
                $jmlPekan = (int) ($_POST['pekan_total'][$idx] ?? 0);
                $pekanTdkEfektif = (int) ($_POST['pekan_non_efektif'][$idx] ?? 0);
                $pekanEfektif = (int) ($_POST['pekan_efektif'][$idx] ?? max(0, $jmlPekan - $pekanTdkEfektif));
                $ket = trim($_POST['pekan_ket'][$idx] ?? '');

                $pekanRows[] = [
                    'bulan' => $namaBulan,
                    'pekan_total' => $jmlPekan,
                    'pekan_non_efektif' => $pekanTdkEfektif,
                    'pekan_efektif' => $pekanEfektif,
                    'keterangan' => $ket
                ];

                $totalPekanSemua += $jmlPekan;
                $totalPekanTidakEfektif += $pekanTdkEfektif;
                $totalPekanEfektif += $pekanEfektif;
            }
        }

        $totalJpEfektif = $totalPekanEfektif * $jpPerMinggu;

        $filePath = $item['file_lampiran'];
        if (!empty($_FILES['file_lampiran']['name'])) {
            $newPath = PerangkatModel::uploadFile($_FILES['file_lampiran'], 'heb');
            if ($newPath) { $filePath = $newPath; }
        }

        $status = $item['status'];
        if (isset($_POST['ajukan'])) {
            $status = 'diajukan';
        }

        $data = [
            'unit' => $_POST['unit'] ?? $item['unit'] ?? 'SD',
            'kaldik_id' => !empty($_POST['kaldik_id']) ? (int)$_POST['kaldik_id'] : null,
            'judul' => trim($_POST['judul']),
            'tahun_akademik_id' => (int) $_POST['tahun_akademik_id'],
            'semester' => $_POST['semester'],
            'mata_pelajaran' => trim($_POST['mata_pelajaran']),
            'tingkat_kelas' => trim($_POST['tingkat_kelas']),
            'fase' => trim($_POST['fase'] ?? ''),
            'alokasi_waktu' => "{$totalJpEfektif} JP ({$jpPerMinggu} JP/Minggu)",
            'guru_id' => !empty($_POST['guru_id']) ? (int)$_POST['guru_id'] : null,
            'guru_nama' => trim($_POST['guru_nama'] ?? Auth::name() ?? 'Administrator'),
            'guru_nip' => trim($_POST['guru_nip'] ?? ''),
            'konten_json' => [
                'jp_per_minggu' => $jpPerMinggu,
                'pekan_rows' => $pekanRows,
                'total_pekan_semua' => $totalPekanSemua,
                'total_pekan_tidak_efektif' => $totalPekanTidakEfektif,
                'total_pekan_efektif' => $totalPekanEfektif,
                'total_jp_efektif' => $totalJpEfektif,
                'distribusi_jp' => [
                    'jp_kbm' => (int) ($_POST['jp_kbm'] ?? round($totalJpEfektif * 0.85)),
                    'jp_penilaian' => (int) ($_POST['jp_penilaian'] ?? round($totalJpEfektif * 0.10)),
                    'jp_cadangan' => (int) ($_POST['jp_cadangan'] ?? max(0, $totalJpEfektif - round($totalJpEfektif * 0.95)))
                ]
            ],
            'file_lampiran' => $filePath,
            'status' => $status
        ];

        PerangkatModel::update($id, $data);

        if (isset($_POST['ajukan']) && $item['status'] !== 'diajukan') {
            PerangkatModel::updateStatus($id, 'diajukan', 'Diajukan untuk peninjauan/verifikasi.', Auth::id(), Auth::name());
        }

        Response::withSuccess(url('kelola-perangkat-pembelajaran/heb'), 'Data Hari Efektif Belajar (HEB) berhasil diperbarui.');
    }

    public static function detailHeb(int $id): void
    {
        $item = PerangkatModel::getById($id);
        if (!$item) { Response::withError(url('kelola-perangkat-pembelajaran/heb'), 'Data tidak ditemukan.'); return; }

        $konten = !empty($item['konten_json']) ? json_decode($item['konten_json'], true) : [];
        $logs = PerangkatModel::getLogs($id);

        self::view('heb/detail', [
            'item' => $item,
            'konten' => $konten,
            'logs' => $logs,
            'can_approve' => PerangkatModel::canApprove()
        ], 'Detail Hari Efektif Belajar (HEB)', [
            ['label' => 'Perangkat Pembelajaran', 'url' => url('kelola-perangkat-pembelajaran')],
            ['label' => 'HEB', 'url' => url('kelola-perangkat-pembelajaran/heb')],
            ['label' => 'Detail']
        ]);
    }

    public static function cetakHeb(int $id): void
    {
        $item = PerangkatModel::getById($id);
        if (!$item) { Response::withError(url('kelola-perangkat-pembelajaran/heb'), 'Data tidak ditemukan.'); return; }
        
        $konten = !empty($item['konten_json']) ? json_decode($item['konten_json'], true) : [];
        include BASE_PATH . '/modules/kelola-perangkat-pembelajaran/views/heb/cetak.php';
        exit;
    }

    // ── 3.5. CAPAIAN PEMBELAJARAN & ALUR TUJUAN PEMBELAJARAN (CP & ATP) ──
    public static function getGuruPenugasanMap(): array
    {
        $db = Database::getInstance();
        $penugasanRows = $db->findAll("
            SELECT m.pegawai_id, m.mata_pelajaran, m.nama_kelas, m.jumlah_jp, p.nama as nama_guru, p.gelar, p.niy
            FROM pegawai_penugasan_mengajar m
            JOIN pegawai p ON p.id = m.pegawai_id
            WHERE m.nama_kelas IS NOT NULL AND m.nama_kelas != ''
            ORDER BY p.nama ASC, m.nama_kelas ASC, m.mata_pelajaran ASC
        ");

        $guruPenugasanMap = [];
        foreach ($penugasanRows as $r) {
            $pId = (int)$r['pegawai_id'];
            if (!isset($guruPenugasanMap[$pId])) {
                $guruPenugasanMap[$pId] = [
                    'nama' => trim(($r['nama_guru'] ?? '') . (!empty($r['gelar']) ? ', ' . $r['gelar'] : '')),
                    'niy' => $r['niy'] ?? '',
                    'assignments' => []
                ];
            }
            
            $fase = 'Fase B';
            $kls = strtolower($r['nama_kelas']);
            if (strpos($kls, 'paud') !== false || strpos($kls, 'tk') !== false) {
                $fase = 'Fase Fondasi';
            } elseif (preg_match('/kelas\s*(1|2|i|ii)\b/i', $kls)) {
                $fase = 'Fase A';
            } elseif (preg_match('/kelas\s*(3|4|iii|iv)\b/i', $kls)) {
                $fase = 'Fase B';
            } elseif (preg_match('/kelas\s*(5|6|v|vi)\b/i', $kls)) {
                $fase = 'Fase C';
            } elseif (preg_match('/kelas\s*(7|8|9|vii|viii|ix)\b/i', $kls)) {
                $fase = 'Fase D';
            } elseif (preg_match('/kelas\s*(10|x)\b/i', $kls)) {
                $fase = 'Fase E';
            } elseif (preg_match('/kelas\s*(11|12|xi|xii)\b/i', $kls)) {
                $fase = 'Fase F';
            }

            $guruPenugasanMap[$pId]['assignments'][] = [
                'mapel' => $r['mata_pelajaran'],
                'kelas' => $r['nama_kelas'],
                'jp' => (int)$r['jumlah_jp'],
                'fase' => $fase
            ];
        }

        return $guruPenugasanMap;
    }

    public static function cpatp(): void
    {
        $filters = self::getCommonFilters();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;
        $filterIsActive = $_GET['is_active'] ?? '';

        $filterArgs = [
            'tipe' => 'cpatp_group',
            'unit' => $filters['filter_unit'],
            'is_active' => $filterIsActive,
            'tahun_akademik_id' => $filters['filter_ta'],
            'semester' => $filters['filter_semester'],
            'status' => $filters['filter_status'],
            'search' => $filters['search']
        ];

        $items = PerangkatModel::getAll($filterArgs, $limit, $offset);
        $total = PerangkatModel::countAll($filterArgs);
        $totalPages = max(1, ceil($total / $limit));

        self::view('cpatp/index', array_merge($filters, [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'filter_is_active' => $filterIsActive,
            'can_approve' => PerangkatModel::canApprove()
        ]), 'Grup CP & ATP', [
            ['label' => 'Perangkat Pembelajaran', 'url' => url('kelola-perangkat-pembelajaran')],
            ['label' => 'CP & ATP']
        ]);
    }

    public static function createCpatpGroup(): void
    {
        if (!PerangkatModel::canApprove()) {
            Response::withError(url('kelola-perangkat-pembelajaran/cpatp'), 'Anda tidak memiliki akses untuk membuat Grup.');
            return;
        }
        $filters = self::getCommonFilters();
        self::view('cpatp/group_create', $filters, 'Buat Grup CP & ATP Baru', [
            ['label' => 'Perangkat Pembelajaran', 'url' => url('kelola-perangkat-pembelajaran')],
            ['label' => 'CP & ATP', 'url' => url('kelola-perangkat-pembelajaran/cpatp')],
            ['label' => 'Buat Grup']
        ]);
    }

    public static function getPenugasanAjax(int $guruId): void
    {
        $db = Database::getInstance();
        $sql = "SELECT DISTINCT ppm.nama_kelas as kelas, ppm.mata_pelajaran, ppm.jumlah_jp 
                FROM pegawai_penugasan_mengajar ppm
                JOIN pegawai_penugasan pp ON pp.id = ppm.penugasan_id
                JOIN penugasan_grup pg ON pg.id = pp.grup_id
                WHERE pp.pegawai_id = ? AND pg.is_active = 1
                ORDER BY ppm.nama_kelas, ppm.mata_pelajaran";
                
        $results = $db->findAll($sql, [$guruId]);
        header('Content-Type: application/json');
        echo json_encode($results);
    }

    public static function getJadwalHariAjax(int $guruId): void
    {
        $mapel = trim($_GET['mapel'] ?? '');
        $kelas = trim($_GET['kelas'] ?? '');
        $unitParam = trim($_GET['unit'] ?? '');
        $semesterParam = trim($_GET['semester'] ?? '');
        $tahunParam = trim($_GET['tahun_ajaran'] ?? '');
        
        $db = Database::getInstance();
        $sql = "SELECT DISTINCT hari 
                FROM jadwal_pelajaran 
                WHERE pegawai_id = ? AND LOWER(TRIM(mata_pelajaran)) = LOWER(?) AND LOWER(TRIM(nama_kelas)) = LOWER(?)";
                
        $results = $db->findAll($sql, [$guruId, $mapel, $kelas]);
        
        // Fetch active tahun akademik to know the year boundary
        $tahun = $db->find("SELECT nama_tahun, tanggal_mulai, tanggal_selesai FROM tahun_akademik WHERE is_active = 1 LIMIT 1");
        
        require_once BASE_PATH . '/modules/kelola-perangkat-pembelajaran/models/HariEfektifEngine.php';
        
        $unit = !empty($unitParam) ? $unitParam : 'SD';
        if (empty($unitParam)) {
            $pegawai = $db->find("SELECT unit_tugas FROM pegawai WHERE id = ?", [$guruId]);
            if ($pegawai && !empty($pegawai['unit_tugas'])) {
                $unit = $pegawai['unit_tugas'];
            }
        }
        
        // Parse nama_tahun to get tahunAjaran and semester
        $namaTahun = $tahun['nama_tahun'] ?? '2026/2027 Ganjil';
        $parts = explode(' ', $namaTahun);
        $tahunAjaran = !empty($tahunParam) ? $tahunParam : ($parts[0] ?? '2026/2027');
        $semester = !empty($semesterParam) ? $semesterParam : ($parts[1] ?? 'Ganjil');
        
        $engine = new HariEfektifEngine();
        $hesData = $engine->hitung(
            $unit,
            $tahunAjaran,
            $semester,
            $guruId,
            $kelas,
            $mapel
        );
        
        header('Content-Type: application/json');
        echo json_encode([
            'hari_mengajar' => array_column($results, 'hari'),
            'tahun_akademik' => $tahun,
            'heb_data' => $hesData['heb']['rows'] ?? []
        ]);
    }

    public static function storeCpatpGroup(): void
    {
        if (!CSRF::validate()) { Response::withError(url('kelola-perangkat-pembelajaran/cpatp'), 'Token CSRF tidak valid.'); return; }
        if (!PerangkatModel::canApprove()) { Response::withError(url('kelola-perangkat-pembelajaran/cpatp'), 'Akses ditolak.'); return; }

        $validator = Validator::make($_POST)
            ->required('judul', 'Judul Grup')
            ->required('unit', 'Unit Sekolah')
            ->required('tahun_akademik_id', 'Tahun Ajaran')
            ->required('semester', 'Semester');
            
        if ($validator->fails()) { Response::backWithErrors($validator->errors(), $_POST); return; }

        $data = [
            'tipe' => 'cpatp_group',
            'judul' => $_POST['judul'],
            'unit' => $_POST['unit'],
            'tahun_akademik_id' => (int) $_POST['tahun_akademik_id'],
            'semester' => $_POST['semester'],
            'guru_id' => 0,
            'guru_nama' => '',
            'guru_nip' => '',
            'mata_pelajaran' => '',
            'tingkat_kelas' => '',
            'fase' => null,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'created_by' => Auth::id(),
            'status' => 'disetujui' // group is automatically active/approved
        ];

        if (PerangkatModel::create($data)) {
            Response::withSuccess(url('kelola-perangkat-pembelajaran/cpatp'), 'Grup CP & ATP berhasil dibuat.');
        } else {
            Response::withError(url('kelola-perangkat-pembelajaran/cpatp'), 'Gagal menyimpan grup.');
        }
    }

    public static function deleteCpatpGroup(int $id): void
    {
        if (!CSRF::validate()) { Response::withError(url('kelola-perangkat-pembelajaran/cpatp'), 'Token CSRF tidak valid.'); return; }
        if (!PerangkatModel::canApprove()) { Response::withError(url('kelola-perangkat-pembelajaran/cpatp'), 'Akses ditolak.'); return; }
        
        $item = PerangkatModel::getById($id);
        if (!$item || $item['tipe'] !== 'cpatp_group') {
            Response::withError(url('kelola-perangkat-pembelajaran/cpatp'), 'Grup tidak ditemukan.'); return;
        }
        
        $db = Database::getInstance();
        $db->getConnection()->exec("DELETE FROM perangkat_pembelajaran WHERE tipe = 'cpatp' AND unit = '{$item['unit']}' AND tahun_akademik_id = {$item['tahun_akademik_id']} AND semester = '{$item['semester']}' AND guru_id = {$item['guru_id']} AND mata_pelajaran = '{$item['mata_pelajaran']}' AND tingkat_kelas = '{$item['tingkat_kelas']}'");
        
        if (PerangkatModel::delete($id)) {
            Response::withSuccess(url('kelola-perangkat-pembelajaran/cpatp'), 'Grup beserta dokumen CP & ATP di dalamnya berhasil dihapus.');
        } else {
            Response::withError(url('kelola-perangkat-pembelajaran/cpatp'), 'Gagal menghapus grup.');
        }
    }

    public static function cpatpDetailGroup(int $id): void
    {
        $group = PerangkatModel::getById($id);
        if (!$group || $group['tipe'] !== 'cpatp_group') {
            Response::withError(url('kelola-perangkat-pembelajaran/cpatp'), 'Grup tidak ditemukan.');
            return;
        }

        $filters = self::getCommonFilters();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $currentGuruId = null;
        if (class_exists('Auth') && !Auth::isSuperAdmin()) {
            $userPegawai = Database::getInstance()->find("SELECT id FROM pegawai WHERE email = ? OR nama = ?", [Auth::user()['email'] ?? '', Auth::name() ?? '']);
            if ($userPegawai) {
                $currentGuruId = (int)$userPegawai['id'];
            }
        }
        
        $filterArgs = [
            'tipe' => 'cpatp',
            'unit' => $group['unit'],
            'tahun_akademik_id' => $group['tahun_akademik_id'],
            'semester' => $group['semester'],
            'status' => $filters['filter_status'],
            'guru_id' => $group['guru_id'],
            'mata_pelajaran' => $group['mata_pelajaran'],
            'tingkat_kelas' => $group['tingkat_kelas'],
            'search' => $filters['search']
        ];

        $items = PerangkatModel::getAll($filterArgs, $limit, $offset);
        $total = PerangkatModel::countAll($filterArgs);
        $totalPages = max(1, ceil($total / $limit));

        $guruPenugasanMap = self::getGuruPenugasanMap();

        self::view('cpatp/group_detail', array_merge($filters, [
            'group' => $group,
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'can_approve' => PerangkatModel::canApprove(),
            'guruPenugasanMap' => $guruPenugasanMap,
            'currentGuruId' => $currentGuruId
        ]), 'Detail Grup CP & ATP: ' . $group['judul'], [
            ['label' => 'Perangkat Pembelajaran', 'url' => url('kelola-perangkat-pembelajaran')],
            ['label' => 'CP & ATP', 'url' => url('kelola-perangkat-pembelajaran/cpatp')],
            ['label' => 'Detail Grup']
        ]);
    }

    public static function createCpatp(int $groupId): void
    {
        $group = PerangkatModel::getById($groupId);
        if (!$group || $group['tipe'] !== 'cpatp_group') {
            Response::withError(url('kelola-perangkat-pembelajaran/cpatp'), 'Grup CP ATP tidak ditemukan.');
            return;
        }

        $filters = self::getCommonFilters();
        $guruPenugasanMap = self::getGuruPenugasanMap();

        $currentGuruId = null;
        if (class_exists('Auth') && !Auth::isSuperAdmin()) {
            $userPegawai = Database::getInstance()->find("SELECT id FROM pegawai WHERE email = ? OR nama = ?", [Auth::user()['email'] ?? '', Auth::name() ?? '']);
            if ($userPegawai) {
                $currentGuruId = (int)$userPegawai['id'];
            }
        }

        self::view('cpatp/create', array_merge($filters, [
            'group' => $group,
            'guruPenugasanMap' => $guruPenugasanMap,
            'currentGuruId' => $currentGuruId
        ]), 'Buat Dokumen CP & ATP', [
            ['label' => 'Perangkat Pembelajaran', 'url' => url('kelola-perangkat-pembelajaran')],
            ['label' => 'CP & ATP', 'url' => url('kelola-perangkat-pembelajaran/cpatp')],
            ['label' => 'Detail Grup', 'url' => url('kelola-perangkat-pembelajaran/cpatp/group/' . $groupId)],
            ['label' => 'Buat Baru']
        ]);
    }

    public static function storeCpatp(int $groupId): void
    {
        if (!CSRF::validate()) { Response::withError(url('kelola-perangkat-pembelajaran/cpatp'), 'Token CSRF tidak valid.'); return; }

        $group = PerangkatModel::getById($groupId);
        if (!$group || $group['tipe'] !== 'cpatp_group') {
            Response::withError(url('kelola-perangkat-pembelajaran/cpatp'), 'Grup CP ATP tidak ditemukan.');
            return;
        }

        $validator = Validator::make($_POST)
            ->required('judul', 'Judul CP & ATP')
            ->required('guru_id', 'Guru Pengampu')
            ->required('mata_pelajaran', 'Mata Pelajaran')
            ->required('tingkat_kelas', 'Kelas');
            
        if ($validator->fails()) { Response::backWithErrors($validator->errors(), $_POST); return; }

        $cpatpRows = [];
        if (!empty($_POST['row_elemen'])) {
            foreach ($_POST['row_elemen'] as $idx => $elemen) {
                if (empty(trim($elemen)) && empty(trim($_POST['row_cp'][$idx] ?? '')) && empty(trim($_POST['row_tp'][$idx] ?? ''))) {
                    continue;
                }

                $kktpList = [];
                if (!empty($_POST['row_kktp'][$idx])) {
                    foreach ($_POST['row_kktp'][$idx] as $kIdx => $kText) {
                        if (!empty(trim($kText))) {
                            $kktpList[] = [
                                'kktp' => trim($kText),
                                'bulan' => trim($_POST['row_bulan'][$idx][$kIdx] ?? 'Juli'),
                                'pekan' => trim($_POST['row_pekan'][$idx][$kIdx] ?? '1')
                            ];
                        }
                    }
                }
                if (empty($kktpList)) {
                    $kktpList[] = [
                        'kktp' => '',
                        'bulan' => 'Juli',
                        'pekan' => '1'
                    ];
                }

                $cpatpRows[] = [
                    'elemen' => trim($elemen),
                    'cp' => trim($_POST['row_cp'][$idx] ?? ''),
                    'tp' => trim($_POST['row_tp'][$idx] ?? ''),
                    'kktp_list' => $kktpList
                ];
            }
        }

        $filePath = null;
        if (!empty($_FILES['file_lampiran']['name'])) {
            $filePath = PerangkatModel::uploadFile($_FILES['file_lampiran'], 'cpatp');
        }

        $alokasiWaktu = trim($_POST['alokasi_waktu'] ?? '');

        $data = [
            'tipe' => 'cpatp',
            'unit' => $group['unit'],
            'judul' => trim($_POST['judul']),
            'tahun_akademik_id' => $group['tahun_akademik_id'],
            'semester' => $group['semester'],
            'mata_pelajaran' => trim($_POST['mata_pelajaran']),
            'tingkat_kelas' => trim($_POST['tingkat_kelas']),
            'fase' => trim($_POST['fase'] ?? ''),
            'alokasi_waktu' => $alokasiWaktu,
            'guru_id' => !empty($_POST['guru_id']) ? (int)$_POST['guru_id'] : null,
            'guru_nama' => trim($_POST['guru_nama'] ?? Auth::name() ?? 'Administrator'),
            'guru_nip' => trim($_POST['guru_nip'] ?? ''),
            'konten_json' => [
                'cpatp_rows' => $cpatpRows,
                'sumber_belajar' => trim($_POST['sumber_belajar'] ?? ''),
                'glosarium' => trim($_POST['glosarium'] ?? '')
            ],
            'file_lampiran' => $filePath,
            'status' => isset($_POST['ajukan']) ? 'diajukan' : 'draft',
            'created_by' => Auth::id() ?? 1
        ];

        $id = PerangkatModel::create($data);
        if (isset($_POST['ajukan'])) {
            PerangkatModel::updateStatus($id, 'diajukan', 'Pengajuan awal saat pembuatan.', Auth::id(), Auth::name());
        }

        Response::withSuccess(url('kelola-perangkat-pembelajaran/cpatp/group/' . $groupId), 'Dokumen CP & ATP berhasil disimpan.');
    }

    public static function editCpatp(int $id): void
    {
        $item = PerangkatModel::getById($id);
        if (!$item || $item['tipe'] !== 'cpatp') {
            Response::withError(url('kelola-perangkat-pembelajaran/cpatp'), 'Data CP & ATP tidak ditemukan.');
            return;
        }

        $filters = self::getCommonFilters();
        $konten = !empty($item['konten_json']) ? json_decode($item['konten_json'], true) : [];
        $logs = PerangkatModel::getLogs($id);

        $db = Database::getInstance();
        $group = $db->find("SELECT id FROM perangkat_pembelajaran WHERE tipe = 'cpatp_group' AND unit = ? AND tahun_akademik_id = ? AND semester = ? LIMIT 1", 
            [$item['unit'], $item['tahun_akademik_id'], $item['semester']]
        );

        self::view('cpatp/edit', array_merge($filters, [
            'item' => $item,
            'konten' => $konten,
            'logs' => $logs,
            'group' => $group,
            'currentGuru' => $item['guru_id']
        ]), 'Edit Dokumen CP & ATP', [
            ['label' => 'Perangkat Pembelajaran', 'url' => url('kelola-perangkat-pembelajaran')],
            ['label' => 'CP & ATP', 'url' => url('kelola-perangkat-pembelajaran/cpatp')],
            ['label' => 'Edit Data']
        ]);
    }

    public static function updateCpatp(int $id): void
    {
        if (!CSRF::validate()) { Response::withError(url('kelola-perangkat-pembelajaran/cpatp'), 'Token CSRF tidak valid.'); return; }

        $item = PerangkatModel::getById($id);
        if (!$item || $item['tipe'] !== 'cpatp') {
            Response::withError(url('kelola-perangkat-pembelajaran/cpatp'), 'Data tidak ditemukan.');
            return;
        }

        $validator = Validator::make($_POST)
            ->required('judul', 'Judul CP & ATP')
            ->required('guru_id', 'Guru Pengampu')
            ->required('mata_pelajaran', 'Mata Pelajaran')
            ->required('tingkat_kelas', 'Kelas');
            
        if ($validator->fails()) { Response::backWithErrors($validator->errors(), $_POST); return; }

        $cpatpRows = [];
        if (!empty($_POST['row_elemen'])) {
            foreach ($_POST['row_elemen'] as $idx => $elemen) {
                if (empty(trim($elemen)) && empty(trim($_POST['row_cp'][$idx] ?? '')) && empty(trim($_POST['row_tp'][$idx] ?? ''))) {
                    continue;
                }

                $kktpList = [];
                if (!empty($_POST['row_kktp'][$idx])) {
                    foreach ($_POST['row_kktp'][$idx] as $kIdx => $kText) {
                        if (!empty(trim($kText))) {
                            $kktpList[] = [
                                'kktp' => trim($kText),
                                'bulan' => trim($_POST['row_bulan'][$idx][$kIdx] ?? 'Juli'),
                                'pekan' => trim($_POST['row_pekan'][$idx][$kIdx] ?? '1')
                            ];
                        }
                    }
                }
                if (empty($kktpList)) {
                    $kktpList[] = [
                        'kktp' => '',
                        'bulan' => 'Juli',
                        'pekan' => '1'
                    ];
                }

                $cpatpRows[] = [
                    'elemen' => trim($elemen),
                    'cp' => trim($_POST['row_cp'][$idx] ?? ''),
                    'tp' => trim($_POST['row_tp'][$idx] ?? ''),
                    'kktp_list' => $kktpList
                ];
            }
        }

        $filePath = $item['file_lampiran'];
        if (!empty($_FILES['file_lampiran']['name'])) {
            $uploaded = PerangkatModel::uploadFile($_FILES['file_lampiran'], 'cpatp');
            if ($uploaded) $filePath = $uploaded;
        }

        $alokasiWaktu = trim($_POST['alokasi_waktu'] ?? $item['alokasi_waktu']);

        $data = [
            'unit' => $_POST['unit'] ?? $item['unit'],
            'judul' => trim($_POST['judul']),
            'tahun_akademik_id' => (int) $_POST['tahun_akademik_id'],
            'semester' => $_POST['semester'] ?? $item['semester'],
            'mata_pelajaran' => trim($_POST['mata_pelajaran']),
            'tingkat_kelas' => trim($_POST['tingkat_kelas']),
            'fase' => trim($_POST['fase'] ?? $item['fase']),
            'alokasi_waktu' => $alokasiWaktu,
            'guru_id' => !empty($_POST['guru_id']) ? (int)$_POST['guru_id'] : $item['guru_id'],
            'guru_nama' => trim($_POST['guru_nama'] ?? $item['guru_nama']),
            'guru_nip' => trim($_POST['guru_nip'] ?? $item['guru_nip']),
            'konten_json' => [
                'cpatp_rows' => $cpatpRows,
                'sumber_belajar' => trim($_POST['sumber_belajar'] ?? ''),
                'glosarium' => trim($_POST['glosarium'] ?? '')
            ],
            'file_lampiran' => $filePath
        ];

        if (isset($_POST['ajukan']) && ($item['status'] === 'draft' || $item['status'] === 'ditolak')) {
            $data['status'] = 'diajukan';
            PerangkatModel::updateStatus($id, 'diajukan', 'Diajukan kembali setelah perbaikan.', Auth::id(), Auth::name());
        }

        PerangkatModel::update($id, $data);
        
        $db = Database::getInstance();
        $group = $db->find("SELECT id FROM perangkat_pembelajaran WHERE tipe = 'cpatp_group' AND unit = ? AND tahun_akademik_id = ? AND semester = ? AND guru_id = ? AND mata_pelajaran = ? AND tingkat_kelas = ? LIMIT 1", 
            [$item['unit'], $item['tahun_akademik_id'], $item['semester'], $item['guru_id'], $item['mata_pelajaran'], $item['tingkat_kelas']]
        );

        if (!empty($group)) {
            Response::withSuccess(url("kelola-perangkat-pembelajaran/cpatp/group/{$group['id']}"), 'Dokumen CP & ATP berhasil diperbarui.');
        } else {
            Response::withSuccess(url('kelola-perangkat-pembelajaran/cpatp'), 'Dokumen CP & ATP berhasil diperbarui.');
        }
    }

    public static function detailCpatp(int $id): void
    {
        $item = PerangkatModel::getById($id);
        if (!$item || $item['tipe'] !== 'cpatp') {
            Response::withError(url('kelola-perangkat-pembelajaran/cpatp'), 'Data tidak ditemukan.');
            return;
        }

        $konten = !empty($item['konten_json']) ? json_decode($item['konten_json'], true) : [];
        $logs = PerangkatModel::getLogs($id);
        
        $db = Database::getInstance();
        $group = $db->find("SELECT id FROM perangkat_pembelajaran WHERE tipe = 'cpatp_group' AND unit = ? AND tahun_akademik_id = ? AND semester = ? AND guru_id = ? AND mata_pelajaran = ? AND tingkat_kelas = ? LIMIT 1", 
            [$item['unit'], $item['tahun_akademik_id'], $item['semester'], $item['guru_id'], $item['mata_pelajaran'], $item['tingkat_kelas']]
        );

        self::view('cpatp/detail', [
            'item' => $item,
            'konten' => $konten,
            'logs' => $logs,
            'group' => $group,
            'can_approve' => PerangkatModel::canApprove()
        ], 'Detail Capaian Pembelajaran & Alur Tujuan Pembelajaran (CP & ATP)', [
            ['label' => 'Perangkat Pembelajaran', 'url' => url('kelola-perangkat-pembelajaran')],
            ['label' => 'CP & ATP', 'url' => url('kelola-perangkat-pembelajaran/cpatp')],
            ['label' => 'Detail']
        ]);
    }

    public static function cetakCpatp(int $id): void
    {
        require_once BASE_PATH . '/modules/kelola-perangkat-pembelajaran/models/HariEfektifEngine.php';
        $item = PerangkatModel::getById($id);
        if (!$item) { Response::withError(url('kelola-perangkat-pembelajaran/cpatp'), 'Data tidak ditemukan.'); return; }

        $engine = new HariEfektifEngine();
        $unitProfile = $engine->getUnitProfile($item['unit'] ?? 'SD');

        $konten = !empty($item['konten_json']) ? json_decode($item['konten_json'], true) : [];
        include BASE_PATH . '/modules/kelola-perangkat-pembelajaran/views/cpatp/cetak.php';
        exit;
    }

    public static function deleteCpatp(int $id): void
    {
        if (!CSRF::validate()) { Response::withError(url('kelola-perangkat-pembelajaran/cpatp'), 'Token CSRF tidak valid.'); return; }
        PerangkatModel::delete($id);
        Response::withSuccess(url('kelola-perangkat-pembelajaran/cpatp'), 'Dokumen CP & ATP berhasil dihapus.');
    }

    // ── 4. PROGRAM TAHUNAN (PROTA) ──────────────────────────
    public static function prota(): void
    {
        $filters = self::getCommonFilters();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $filterArgs = [
            'tipe' => 'prota',
            'unit' => $filters['filter_unit'],
            'tahun_akademik_id' => $filters['filter_ta'],
            'status' => $filters['filter_status'],
            'search' => $filters['search']
        ];

        $items = PerangkatModel::getAll($filterArgs, $limit, $offset);
        $total = PerangkatModel::countAll($filterArgs);
        $totalPages = max(1, ceil($total / $limit));

        self::view('prota/index', array_merge($filters, [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'can_approve' => PerangkatModel::canApprove()
        ]), 'Program Tahunan (Prota)', [
            ['label' => 'Perangkat Pembelajaran', 'url' => url('kelola-perangkat-pembelajaran')],
            ['label' => 'Program Tahunan (Prota)']
        ]);
    }

    public static function createProta(): void
    {
        $filters = self::getCommonFilters();
        self::view('prota/create', $filters, 'Buat Program Tahunan (Prota)', [
            ['label' => 'Perangkat Pembelajaran', 'url' => url('kelola-perangkat-pembelajaran')],
            ['label' => 'Prota', 'url' => url('kelola-perangkat-pembelajaran/prota')],
            ['label' => 'Buat Baru']
        ]);
    }

    public static function storeProta(): void
    {
        if (!CSRF::validate()) { Response::withError(url('kelola-perangkat-pembelajaran/prota'), 'Token CSRF tidak valid.'); return; }

        $validator = Validator::make($_POST)
            ->required('judul', 'Judul Dokumen Prota')
            ->required('unit', 'Unit Sekolah')
            ->required('tahun_akademik_id', 'Tahun Ajaran')
            ->required('mata_pelajaran', 'Mata Pelajaran')
            ->required('tingkat_kelas', 'Tingkat / Kelas');
            
        if ($validator->fails()) { Response::backWithErrors($validator->errors(), $_POST); return; }

        $materiList = [];
        $totalJpSmt1 = 0;
        $totalJpSmt2 = 0;

        if (!empty($_POST['materi_pokok'])) {
            foreach ($_POST['materi_pokok'] as $idx => $materi) {
                if (!empty(trim($materi))) {
                    $jp1 = (int) ($_POST['jp_smt1'][$idx] ?? 0);
                    $jp2 = (int) ($_POST['jp_smt2'][$idx] ?? 0);
                    $cp = trim($_POST['cp_kd'][$idx] ?? '');
                    $ket = trim($_POST['materi_ket'][$idx] ?? '');

                    $materiList[] = [
                        'cp_kd' => $cp,
                        'materi_pokok' => $materi,
                        'jp_smt1' => $jp1,
                        'jp_smt2' => $jp2,
                        'total_jp' => ($jp1 + $jp2),
                        'keterangan' => $ket
                    ];

                    $totalJpSmt1 += $jp1;
                    $totalJpSmt2 += $jp2;
                }
            }
        }

        $totalJpTahun = $totalJpSmt1 + $totalJpSmt2;

        $filePath = null;
        if (!empty($_FILES['file_lampiran']['name'])) {
            $filePath = PerangkatModel::uploadFile($_FILES['file_lampiran'], 'prota');
        }

        $data = [
            'tipe' => 'prota',
            'unit' => $_POST['unit'] ?? 'SD',
            'judul' => trim($_POST['judul']),
            'tahun_akademik_id' => (int) $_POST['tahun_akademik_id'],
            'semester' => 'Ganjil', // Prota covers both semesters
            'mata_pelajaran' => trim($_POST['mata_pelajaran']),
            'tingkat_kelas' => trim($_POST['tingkat_kelas']),
            'fase' => trim($_POST['fase'] ?? ''),
            'alokasi_waktu' => "{$totalJpTahun} JP (Smt 1: {$totalJpSmt1} JP, Smt 2: {$totalJpSmt2} JP)",
            'guru_id' => !empty($_POST['guru_id']) ? (int)$_POST['guru_id'] : null,
            'guru_nama' => trim($_POST['guru_nama'] ?? Auth::name() ?? 'Administrator'),
            'guru_nip' => trim($_POST['guru_nip'] ?? ''),
            'konten_json' => [
                'capaian_umum' => trim($_POST['capaian_umum'] ?? ''),
                'materi_list' => $materiList,
                'total_jp_smt1' => $totalJpSmt1,
                'total_jp_smt2' => $totalJpSmt2,
                'total_jp_tahun' => $totalJpTahun
            ],
            'file_lampiran' => $filePath,
            'status' => isset($_POST['ajukan']) ? 'diajukan' : 'draft',
            'created_by' => Auth::id() ?? 1
        ];

        $id = PerangkatModel::create($data);
        if (isset($_POST['ajukan'])) {
            PerangkatModel::updateStatus($id, 'diajukan', 'Pengajuan awal saat pembuatan.', Auth::id(), Auth::name());
        }

        Response::withSuccess(url('kelola-perangkat-pembelajaran/prota'), 'Program Tahunan (Prota) berhasil disimpan.');
    }

    public static function editProta(int $id): void
    {
        $item = PerangkatModel::getById($id);
        if (!$item || $item['tipe'] !== 'prota') {
            Response::withError(url('kelola-perangkat-pembelajaran/prota'), 'Data Prota tidak ditemukan.');
            return;
        }

        $filters = self::getCommonFilters();
        $konten = !empty($item['konten_json']) ? json_decode($item['konten_json'], true) : [];
        $logs = PerangkatModel::getLogs($id);

        self::view('prota/edit', array_merge($filters, [
            'item' => $item,
            'konten' => $konten,
            'logs' => $logs
        ]), 'Edit Program Tahunan (Prota)', [
            ['label' => 'Perangkat Pembelajaran', 'url' => url('kelola-perangkat-pembelajaran')],
            ['label' => 'Prota', 'url' => url('kelola-perangkat-pembelajaran/prota')],
            ['label' => 'Edit']
        ]);
    }

    public static function updateProta(int $id): void
    {
        if (!CSRF::validate()) { Response::withError(url('kelola-perangkat-pembelajaran/prota'), 'Token CSRF tidak valid.'); return; }

        $item = PerangkatModel::getById($id);
        if (!$item || $item['tipe'] !== 'prota') {
            Response::withError(url('kelola-perangkat-pembelajaran/prota'), 'Data Prota tidak ditemukan.');
            return;
        }

        $validator = Validator::make($_POST)
            ->required('judul', 'Judul Dokumen Prota')
            ->required('unit', 'Unit Sekolah')
            ->required('tahun_akademik_id', 'Tahun Ajaran')
            ->required('mata_pelajaran', 'Mata Pelajaran')
            ->required('tingkat_kelas', 'Tingkat / Kelas');
            
        if ($validator->fails()) { Response::backWithErrors($validator->errors(), $_POST); return; }

        $materiList = [];
        $totalJpSmt1 = 0;
        $totalJpSmt2 = 0;

        if (!empty($_POST['materi_pokok'])) {
            foreach ($_POST['materi_pokok'] as $idx => $materi) {
                if (!empty(trim($materi))) {
                    $jp1 = (int) ($_POST['jp_smt1'][$idx] ?? 0);
                    $jp2 = (int) ($_POST['jp_smt2'][$idx] ?? 0);
                    $cp = trim($_POST['cp_kd'][$idx] ?? '');
                    $ket = trim($_POST['materi_ket'][$idx] ?? '');

                    $materiList[] = [
                        'cp_kd' => $cp,
                        'materi_pokok' => $materi,
                        'jp_smt1' => $jp1,
                        'jp_smt2' => $jp2,
                        'total_jp' => ($jp1 + $jp2),
                        'keterangan' => $ket
                    ];

                    $totalJpSmt1 += $jp1;
                    $totalJpSmt2 += $jp2;
                }
            }
        }

        $totalJpTahun = $totalJpSmt1 + $totalJpSmt2;

        $filePath = $item['file_lampiran'];
        if (!empty($_FILES['file_lampiran']['name'])) {
            $newPath = PerangkatModel::uploadFile($_FILES['file_lampiran'], 'prota');
            if ($newPath) { $filePath = $newPath; }
        }

        $status = $item['status'];
        if (isset($_POST['ajukan'])) {
            $status = 'diajukan';
        }

        $data = [
            'unit' => $_POST['unit'] ?? $item['unit'] ?? 'SD',
            'judul' => trim($_POST['judul']),
            'tahun_akademik_id' => (int) $_POST['tahun_akademik_id'],
            'mata_pelajaran' => trim($_POST['mata_pelajaran']),
            'tingkat_kelas' => trim($_POST['tingkat_kelas']),
            'fase' => trim($_POST['fase'] ?? ''),
            'alokasi_waktu' => "{$totalJpTahun} JP (Smt 1: {$totalJpSmt1} JP, Smt 2: {$totalJpSmt2} JP)",
            'guru_id' => !empty($_POST['guru_id']) ? (int)$_POST['guru_id'] : null,
            'guru_nama' => trim($_POST['guru_nama'] ?? Auth::name() ?? 'Administrator'),
            'guru_nip' => trim($_POST['guru_nip'] ?? ''),
            'konten_json' => [
                'capaian_umum' => trim($_POST['capaian_umum'] ?? ''),
                'materi_list' => $materiList,
                'total_jp_smt1' => $totalJpSmt1,
                'total_jp_smt2' => $totalJpSmt2,
                'total_jp_tahun' => $totalJpTahun
            ],
            'file_lampiran' => $filePath,
            'status' => $status
        ];

        PerangkatModel::update($id, $data);

        if (isset($_POST['ajukan']) && $item['status'] !== 'diajukan') {
            PerangkatModel::updateStatus($id, 'diajukan', 'Diajukan untuk peninjauan/verifikasi.', Auth::id(), Auth::name());
        }

        Response::withSuccess(url('kelola-perangkat-pembelajaran/prota'), 'Program Tahunan (Prota) berhasil diperbarui.');
    }

    public static function detailProta(int $id): void
    {
        $item = PerangkatModel::getById($id);
        if (!$item) { Response::withError(url('kelola-perangkat-pembelajaran/prota'), 'Data tidak ditemukan.'); return; }

        $konten = !empty($item['konten_json']) ? json_decode($item['konten_json'], true) : [];
        $logs = PerangkatModel::getLogs($id);

        self::view('prota/detail', [
            'item' => $item,
            'konten' => $konten,
            'logs' => $logs,
            'can_approve' => PerangkatModel::canApprove()
        ], 'Detail Program Tahunan (Prota)', [
            ['label' => 'Perangkat Pembelajaran', 'url' => url('kelola-perangkat-pembelajaran')],
            ['label' => 'Prota', 'url' => url('kelola-perangkat-pembelajaran/prota')],
            ['label' => 'Detail']
        ]);
    }

    public static function cetakProta(int $id): void
    {
        $item = PerangkatModel::getById($id);
        if (!$item) { Response::withError(url('kelola-perangkat-pembelajaran/prota'), 'Data tidak ditemukan.'); return; }
        
        $konten = !empty($item['konten_json']) ? json_decode($item['konten_json'], true) : [];
        include BASE_PATH . '/modules/kelola-perangkat-pembelajaran/views/prota/cetak.php';
        exit;
    }

    // ── 5. PROGRAM SEMESTER (PROSEM) ────────────────────────
    public static function prosem(): void
    {
        $filters = self::getCommonFilters();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $filterArgs = [
            'tipe' => 'prosem_group',
            'unit' => $filters['filter_unit'],
            'tahun_akademik_id' => $filters['filter_ta'],
            'semester' => $filters['filter_semester'],
            'status' => $filters['filter_status'],
            'search' => $filters['search']
        ];

        $items = PerangkatModel::getAll($filterArgs, $limit, $offset);
        $total = PerangkatModel::countAll($filterArgs);
        $totalPages = max(1, ceil($total / $limit));

        self::view('prosem/index', array_merge($filters, [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'can_approve' => PerangkatModel::canApprove()
        ]), 'Program Semester (Prosem)', [
            ['label' => 'Perangkat Pembelajaran', 'url' => url('kelola-perangkat-pembelajaran')],
            ['label' => 'Program Semester (Prosem)']
        ]);
    }

    public static function createProsemGroup(): void
    {
        if (!PerangkatModel::canApprove()) {
            Response::withError(url('kelola-perangkat-pembelajaran/prosem'), 'Anda tidak memiliki akses untuk membuat Grup.');
            return;
        }
        $filters = self::getCommonFilters();

        $db = Database::getInstance();
        $cpatpGroups = $db->findAll("
            SELECT g.id, g.judul, g.unit, g.semester, ta.nama_tahun,
                   (SELECT COUNT(*) FROM perangkat_pembelajaran c WHERE c.tipe = 'cpatp' AND c.unit = g.unit AND c.tahun_akademik_id = g.tahun_akademik_id AND c.semester = g.semester) as doc_count
            FROM perangkat_pembelajaran g
            JOIN tahun_akademik ta ON ta.id = g.tahun_akademik_id
            WHERE g.tipe = 'cpatp_group'
            ORDER BY g.id DESC
        ");

        self::view('prosem/group_create', array_merge($filters, [
            'cpatp_groups' => $cpatpGroups
        ]), 'Buat Grup Program Semester (Prosem) Baru', [
            ['label' => 'Perangkat Pembelajaran', 'url' => url('kelola-perangkat-pembelajaran')],
            ['label' => 'Prosem', 'url' => url('kelola-perangkat-pembelajaran/prosem')],
            ['label' => 'Buat Grup Prosem']
        ]);
    }

    public static function storeProsemGroup(): void
    {
        if (!CSRF::validate()) { Response::withError(url('kelola-perangkat-pembelajaran/prosem'), 'Token CSRF tidak valid.'); return; }
        if (!PerangkatModel::canApprove()) { Response::withError(url('kelola-perangkat-pembelajaran/prosem'), 'Akses ditolak.'); return; }

        $validator = Validator::make($_POST)
            ->required('judul', 'Judul Grup Prosem')
            ->required('unit', 'Unit Sekolah')
            ->required('tahun_akademik_id', 'Tahun Ajaran')
            ->required('semester', 'Semester');
            
        if ($validator->fails()) { Response::backWithErrors($validator->errors(), $_POST); return; }

        $cpatpGroupId = !empty($_POST['cpatp_group_id']) ? (int)$_POST['cpatp_group_id'] : null;

        $data = [
            'tipe' => 'prosem_group',
            'judul' => trim($_POST['judul']),
            'unit' => $_POST['unit'],
            'tahun_akademik_id' => (int) $_POST['tahun_akademik_id'],
            'semester' => $_POST['semester'],
            'guru_id' => 0,
            'guru_nama' => '',
            'guru_nip' => '',
            'mata_pelajaran' => '',
            'tingkat_kelas' => '',
            'fase' => null,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'konten_json' => json_encode(['cpatp_group_id' => $cpatpGroupId]),
            'created_by' => Auth::id(),
            'status' => 'disetujui'
        ];

        $groupId = PerangkatModel::create($data);
        if ($groupId) {
            if ($cpatpGroupId) {
                self::executeGenerateProsemFromCpatp($groupId, $cpatpGroupId);
            }
            Response::withSuccess(url("kelola-perangkat-pembelajaran/prosem/group/{$groupId}"), 'Grup Prosem berhasil dibuat dan dokumen Prosem berhasil di-generate dari CP & ATP.');
        } else {
            Response::withError(url('kelola-perangkat-pembelajaran/prosem'), 'Gagal menyimpan grup Prosem.');
        }
    }

    public static function deleteProsemGroup(int $id): void
    {
        if (!CSRF::validate()) { Response::withError(url('kelola-perangkat-pembelajaran/prosem'), 'Token CSRF tidak valid.'); return; }
        if (!PerangkatModel::canApprove()) { Response::withError(url('kelola-perangkat-pembelajaran/prosem'), 'Akses ditolak.'); return; }
        
        $item = PerangkatModel::getById($id);
        if (!$item || $item['tipe'] !== 'prosem_group') {
            Response::withError(url('kelola-perangkat-pembelajaran/prosem'), 'Grup tidak ditemukan.'); return;
        }
        
        $db = Database::getInstance();
        $db->getConnection()->exec("DELETE FROM perangkat_pembelajaran WHERE tipe = 'prosem' AND unit = '{$item['unit']}' AND tahun_akademik_id = {$item['tahun_akademik_id']} AND semester = '{$item['semester']}'");
        
        if (PerangkatModel::delete($id)) {
            Response::withSuccess(url('kelola-perangkat-pembelajaran/prosem'), 'Grup beserta dokumen Program Semester di dalamnya berhasil dihapus.');
        } else {
            Response::withError(url('kelola-perangkat-pembelajaran/prosem'), 'Gagal menghapus grup.');
        }
    }

    public static function prosemDetailGroup(int $id): void
    {
        $group = PerangkatModel::getById($id);
        if (!$group || $group['tipe'] !== 'prosem_group') {
            Response::withError(url('kelola-perangkat-pembelajaran/prosem'), 'Grup tidak ditemukan.');
            return;
        }

        $filters = self::getCommonFilters();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $currentGuruId = null;
        if (class_exists('Auth') && !Auth::isSuperAdmin()) {
            $userPegawai = Database::getInstance()->find("SELECT id FROM pegawai WHERE email = ? OR nama = ?", [Auth::user()['email'] ?? '', Auth::name() ?? '']);
            if ($userPegawai) {
                $currentGuruId = (int)$userPegawai['id'];
            }
        }
        
        $filterArgs = [
            'tipe' => 'prosem',
            'unit' => $group['unit'],
            'tahun_akademik_id' => $group['tahun_akademik_id'],
            'semester' => $group['semester'],
            'status' => $filters['filter_status'],
            'search' => $filters['search']
        ];

        $items = PerangkatModel::getAll($filterArgs, $limit, $offset);
        $total = PerangkatModel::countAll($filterArgs);
        $totalPages = max(1, ceil($total / $limit));

        $kontenGroup = !empty($group['konten_json']) ? json_decode($group['konten_json'], true) : [];
        $cpatpGroup = null;
        if (!empty($kontenGroup['cpatp_group_id'])) {
            $cpatpGroup = PerangkatModel::getById((int)$kontenGroup['cpatp_group_id']);
        }

        self::view('prosem/group_detail', array_merge($filters, [
            'group' => $group,
            'cpatp_group' => $cpatpGroup,
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'can_approve' => PerangkatModel::canApprove(),
            'currentGuruId' => $currentGuruId
        ]), 'Detail Wadah Program Semester: ' . $group['judul'], [
            ['label' => 'Perangkat Pembelajaran', 'url' => url('kelola-perangkat-pembelajaran')],
            ['label' => 'Prosem', 'url' => url('kelola-perangkat-pembelajaran/prosem')],
            ['label' => 'Detail Grup']
        ]);
    }

    public static function syncProsemFromCpatp(int $id): void
    {
        if (!CSRF::validate()) { Response::withError(url("kelola-perangkat-pembelajaran/prosem/group/{$id}"), 'Token CSRF tidak valid.'); return; }
        
        $group = PerangkatModel::getById($id);
        if (!$group || $group['tipe'] !== 'prosem_group') {
            Response::withError(url('kelola-perangkat-pembelajaran/prosem'), 'Grup tidak ditemukan.');
            return;
        }

        $konten = !empty($group['konten_json']) ? json_decode($group['konten_json'], true) : [];
        $cpatpGroupId = (int)($konten['cpatp_group_id'] ?? 0);

        if (!$cpatpGroupId) {
            // Cari grup CP ATP yang cocok dengan unit, tahun ajaran, dan semester
            $db = Database::getInstance();
            $matchedCpatp = $db->find("
                SELECT id FROM perangkat_pembelajaran 
                WHERE tipe = 'cpatp_group' AND unit = ? AND tahun_akademik_id = ? AND semester = ?
                ORDER BY id DESC LIMIT 1
            ", [$group['unit'], $group['tahun_akademik_id'], $group['semester']]);

            if ($matchedCpatp) {
                $cpatpGroupId = (int)$matchedCpatp['id'];
                $konten['cpatp_group_id'] = $cpatpGroupId;
                PerangkatModel::update($id, ['konten_json' => json_encode($konten)]);
            }
        }

        if (!$cpatpGroupId) {
            Response::withError(url("kelola-perangkat-pembelajaran/prosem/group/{$id}"), 'Tidak ditemukan Grup CP & ATP yang cocok untuk disinkronkan.');
            return;
        }

        $count = self::executeGenerateProsemFromCpatp($id, $cpatpGroupId);
        Response::withSuccess(url("kelola-perangkat-pembelajaran/prosem/group/{$id}"), "Berhasil menyinkronkan {$count} dokumen Program Semester dari CP & ATP.");
    }

    public static function executeGenerateProsemFromCpatp(int $prosemGroupId, int $cpatpGroupId): int
    {
        $db = Database::getInstance();
        $prosemGroup = PerangkatModel::getById($prosemGroupId);
        $cpatpGroup = PerangkatModel::getById($cpatpGroupId);
        
        if (!$prosemGroup || !$cpatpGroup) return 0;

        $cpatpDocs = $db->findAll("
            SELECT * FROM perangkat_pembelajaran 
            WHERE tipe = 'cpatp' 
              AND unit = ? 
              AND tahun_akademik_id = ? 
              AND semester = ?
        ", [$cpatpGroup['unit'], $cpatpGroup['tahun_akademik_id'], $cpatpGroup['semester']]);

        $generatedCount = 0;
        $bulanNames = ($prosemGroup['semester'] === 'Genap')
            ? ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni']
            : ['Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $bulanLower = array_map('strtolower', $bulanNames);

        foreach ($cpatpDocs as $doc) {
            $kontenCp = !empty($doc['konten_json']) ? json_decode($doc['konten_json'], true) : [];
            $cpatpRows = $kontenCp['cpatp_rows'] ?? [];
            if (empty($cpatpRows)) continue;

            $penugasan = $db->find("
                SELECT ppm.jumlah_jp FROM pegawai_penugasan_mengajar ppm
                JOIN pegawai_penugasan pp ON pp.id = ppm.penugasan_id
                WHERE pp.pegawai_id = ? AND ppm.nama_kelas = ? AND ppm.mata_pelajaran = ?
                LIMIT 1
            ", [$doc['guru_id'], $doc['tingkat_kelas'], $doc['mata_pelajaran']]);
            $weeklyJp = (int)($penugasan['jumlah_jp'] ?? 4);
            $jpPerMeeting = max(2, min(4, (int)ceil($weeklyJp / 2)));

            $materiList = [];
            $rowNo = 1;
            $totalJp = 0;

            foreach ($cpatpRows as $cRow) {
                $elemen = trim((string)($cRow['elemen'] ?? ''));
                $tp = trim((string)($cRow['tp'] ?? ''));
                $kktpList = $cRow['kktp_list'] ?? [];

                foreach ($kktpList as $kItem) {
                    $kktpText = trim((string)($kItem['kktp'] ?? ''));
                    if (empty($kktpText)) continue;

                    $bName = strtolower(trim((string)($kItem['bulan'] ?? '')));
                    $bIdx = array_search($bName, $bulanLower);
                    $b = ($bIdx !== false) ? ($bIdx + 1) : 1;

                    $pStr = (string)($kItem['pekan'] ?? '');
                    $w = 1;
                    if (preg_match('/Pekan\s*(\d+)/i', $pStr, $matchW)) {
                        $w = max(1, min(5, (int)$matchW[1]));
                    }

                    $matriks = [];
                    for ($mIdx = 1; $mIdx <= 6; $mIdx++) {
                        $matriks[$mIdx] = [];
                        for ($wIdx = 1; $wIdx <= 5; $wIdx++) {
                            $cellKey = "b{$mIdx}_w{$wIdx}";
                            $isTarget = ($mIdx === $b && $wIdx === $w);
                            $cellVal = $isTarget ? (string)$jpPerMeeting : '';
                            $matriks[$mIdx][$wIdx] = $cellVal;
                            $matriks[$cellKey] = $cellVal;
                        }
                    }

                    $totalJp += $jpPerMeeting;
                    $materiLabel = !empty($elemen) ? "[{$elemen}] {$kktpText}" : $kktpText;

                    $materiList[] = [
                        'no' => $rowNo++,
                        'materi_pokok' => $materiLabel,
                        'tp_materi' => $materiLabel,
                        'alokasi_jp' => $jpPerMeeting,
                        'matriks' => $matriks,
                        'keterangan' => ''
                    ];
                }
            }

            if (!empty($materiList)) {
                $stsMatriks = [];
                for ($mIdx = 1; $mIdx <= 6; $mIdx++) {
                    for ($wIdx = 1; $wIdx <= 5; $wIdx++) {
                        $val = ($mIdx === 3 && $wIdx === 3) ? (string)$jpPerMeeting : '';
                        $stsMatriks[$mIdx][$wIdx] = $val;
                        $stsMatriks["b{$mIdx}_w{$wIdx}"] = $val;
                    }
                }
                $totalJp += $jpPerMeeting;
                $materiList[] = [
                    'no' => $rowNo++,
                    'materi_pokok' => 'Sumatif Tengah Semester (STS)',
                    'tp_materi' => 'Sumatif Tengah Semester (STS)',
                    'alokasi_jp' => $jpPerMeeting,
                    'matriks' => $stsMatriks,
                    'keterangan' => 'Penilaian Tengah Semester'
                ];

                $sasMatriks = [];
                for ($mIdx = 1; $mIdx <= 6; $mIdx++) {
                    for ($wIdx = 1; $wIdx <= 5; $wIdx++) {
                        $val = ($mIdx === 6 && $wIdx === 2) ? (string)$jpPerMeeting : '';
                        $sasMatriks[$mIdx][$wIdx] = $val;
                        $sasMatriks["b{$mIdx}_w{$wIdx}"] = $val;
                    }
                }
                $totalJp += $jpPerMeeting;
                $materiList[] = [
                    'no' => $rowNo++,
                    'materi_pokok' => 'Sumatif Akhir Semester (SAS) & Remedial',
                    'tp_materi' => 'Sumatif Akhir Semester (SAS) & Remedial',
                    'alokasi_jp' => $jpPerMeeting,
                    'matriks' => $sasMatriks,
                    'keterangan' => 'Penilaian Akhir Semester & Remedial'
                ];
            }

            $existing = $db->find("
                SELECT id FROM perangkat_pembelajaran 
                WHERE tipe = 'prosem' 
                  AND unit = ? 
                  AND tahun_akademik_id = ? 
                  AND semester = ? 
                  AND guru_id = ? 
                  AND mata_pelajaran = ? 
                  AND tingkat_kelas = ?
                LIMIT 1
            ", [$doc['unit'], $doc['tahun_akademik_id'], $doc['semester'], $doc['guru_id'], $doc['mata_pelajaran'], $doc['tingkat_kelas']]);

            $prosemData = [
                'tipe' => 'prosem',
                'unit' => $doc['unit'],
                'judul' => "Program Semester (Prosem) {$doc['mata_pelajaran']} {$doc['tingkat_kelas']}",
                'tahun_akademik_id' => $doc['tahun_akademik_id'],
                'semester' => $doc['semester'],
                'mata_pelajaran' => $doc['mata_pelajaran'],
                'tingkat_kelas' => $doc['tingkat_kelas'],
                'fase' => $doc['fase'],
                'alokasi_waktu' => "{$totalJp} JP",
                'guru_id' => $doc['guru_id'],
                'guru_nama' => $doc['guru_nama'],
                'guru_nip' => $doc['guru_nip'],
                'konten_json' => json_encode([
                    'prosem_group_id' => $prosemGroupId,
                    'cpatp_id' => $doc['id'],
                    'prosem_rows' => $materiList,
                    'materi_list' => $materiList,
                    'total_jp' => $totalJp,
                    'bulan_list' => $bulanNames,
                    'bulan_names' => $bulanNames
                ]),
                'status' => 'disetujui',
                'created_by' => (class_exists('Auth') && Auth::id()) ? Auth::id() : 1
            ];

            if ($existing) {
                PerangkatModel::update($existing['id'], $prosemData);
            } else {
                PerangkatModel::create($prosemData);
            }
            $generatedCount++;
        }

        return $generatedCount;
    }

    public static function createProsem(int $groupId = 0): void
    {
        $filters = self::getCommonFilters();
        $group = null;
        if ($groupId > 0) {
            $group = PerangkatModel::getById($groupId);
        }
        self::view('prosem/create', array_merge($filters, [
            'group' => $group,
            'groupId' => $groupId
        ]), 'Buat Program Semester (Prosem)', [
            ['label' => 'Perangkat Pembelajaran', 'url' => url('kelola-perangkat-pembelajaran')],
            ['label' => 'Prosem', 'url' => url('kelola-perangkat-pembelajaran/prosem')],
            ['label' => 'Buat Baru']
        ]);
    }

    public static function storeProsem(int $groupId = 0): void
    {
        if (!CSRF::validate()) { Response::withError(url('kelola-perangkat-pembelajaran/prosem'), 'Token CSRF tidak valid.'); return; }

        $validator = Validator::make($_POST)
            ->required('judul', 'Judul Dokumen Prosem')
            ->required('unit', 'Unit Sekolah')
            ->required('tahun_akademik_id', 'Tahun Ajaran')
            ->required('semester', 'Semester')
            ->required('mata_pelajaran', 'Mata Pelajaran')
            ->required('tingkat_kelas', 'Tingkat / Kelas');
            
        if ($validator->fails()) { Response::backWithErrors($validator->errors(), $_POST); return; }

        $materiList = [];
        $totalJp = 0;

        if (!empty($_POST['materi_pokok'])) {
            foreach ($_POST['materi_pokok'] as $idx => $materi) {
                if (!empty(trim($materi))) {
                    $jp = (int) ($_POST['alokasi_jp'][$idx] ?? $_POST['materi_jp'][$idx] ?? 0);
                    $totalJp += $jp;
                    
                    $matriks = [];
                    for ($b = 1; $b <= 6; $b++) {
                        $matriks[$b] = [];
                        for ($w = 1; $w <= 5; $w++) {
                            $key = "matriks_b{$b}_w{$w}";
                            $val = !empty($_POST[$key][$idx]) ? trim($_POST[$key][$idx]) : '';
                            $matriks[$b][$w] = $val;
                            $matriks["b{$b}_w{$w}"] = $val;
                        }
                    }

                    $materiList[] = [
                        'no' => ($idx + 1),
                        'materi_pokok' => $materi,
                        'tp_materi' => $materi,
                        'alokasi_jp' => $jp,
                        'matriks' => $matriks,
                        'keterangan' => trim($_POST['materi_ket'][$idx] ?? '')
                    ];
                }
            }
        }

        $filePath = null;
        if (!empty($_FILES['file_lampiran']['name'])) {
            $filePath = PerangkatModel::uploadFile($_FILES['file_lampiran'], 'prosem');
        }

        $bulanNames = ($_POST['semester'] === 'Genap')
            ? ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni']
            : ['Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        $data = [
            'tipe' => 'prosem',
            'unit' => $_POST['unit'] ?? 'SD',
            'judul' => trim($_POST['judul']),
            'tahun_akademik_id' => (int) $_POST['tahun_akademik_id'],
            'semester' => $_POST['semester'],
            'mata_pelajaran' => trim($_POST['mata_pelajaran']),
            'tingkat_kelas' => trim($_POST['tingkat_kelas']),
            'fase' => trim($_POST['fase'] ?? ''),
            'alokasi_waktu' => "{$totalJp} JP",
            'guru_id' => !empty($_POST['guru_id']) ? (int)$_POST['guru_id'] : null,
            'guru_nama' => trim($_POST['guru_nama'] ?? Auth::name() ?? 'Administrator'),
            'guru_nip' => trim($_POST['guru_nip'] ?? ''),
            'konten_json' => [
                'prosem_group_id' => $groupId > 0 ? $groupId : null,
                'bulan_names' => $bulanNames,
                'bulan_list' => $bulanNames,
                'materi_list' => $materiList,
                'prosem_rows' => $materiList,
                'total_jp' => $totalJp
            ],
            'file_lampiran' => $filePath,
            'status' => isset($_POST['ajukan']) ? 'diajukan' : 'draft',
            'created_by' => Auth::id() ?? 1
        ];

        $id = PerangkatModel::create($data);
        if (isset($_POST['ajukan'])) {
            PerangkatModel::updateStatus($id, 'diajukan', 'Pengajuan awal saat pembuatan.', Auth::id(), Auth::name());
        }

        $redirectUrl = ($groupId > 0) ? url("kelola-perangkat-pembelajaran/prosem/group/{$groupId}") : url('kelola-perangkat-pembelajaran/prosem');
        Response::withSuccess($redirectUrl, 'Program Semester (Prosem) berhasil disimpan.');
    }

    public static function editProsem(int $id): void
    {
        $item = PerangkatModel::getById($id);
        if (!$item || $item['tipe'] !== 'prosem') {
            Response::withError(url('kelola-perangkat-pembelajaran/prosem'), 'Data Prosem tidak ditemukan.');
            return;
        }

        $filters = self::getCommonFilters();
        $konten = !empty($item['konten_json']) ? json_decode($item['konten_json'], true) : [];
        $logs = PerangkatModel::getLogs($id);

        $groupId = $konten['prosem_group_id'] ?? 0;
        $group = null;
        if ($groupId > 0) {
            $group = PerangkatModel::getById($groupId);
        }

        self::view('prosem/edit', array_merge($filters, [
            'item' => $item,
            'konten' => $konten,
            'logs' => $logs,
            'group' => $group,
            'groupId' => $groupId
        ]), 'Edit Program Semester (Prosem)', [
            ['label' => 'Perangkat Pembelajaran', 'url' => url('kelola-perangkat-pembelajaran')],
            ['label' => 'Prosem', 'url' => url('kelola-perangkat-pembelajaran/prosem')],
            ['label' => 'Edit']
        ]);
    }

    public static function updateProsem(int $id): void
    {
        if (!CSRF::validate()) { Response::withError(url('kelola-perangkat-pembelajaran/prosem'), 'Token CSRF tidak valid.'); return; }

        $item = PerangkatModel::getById($id);
        if (!$item || $item['tipe'] !== 'prosem') {
            Response::withError(url('kelola-perangkat-pembelajaran/prosem'), 'Data Prosem tidak ditemukan.');
            return;
        }

        $validator = Validator::make($_POST)
            ->required('judul', 'Judul Dokumen Prosem')
            ->required('unit', 'Unit Sekolah')
            ->required('tahun_akademik_id', 'Tahun Ajaran')
            ->required('semester', 'Semester')
            ->required('mata_pelajaran', 'Mata Pelajaran')
            ->required('tingkat_kelas', 'Tingkat / Kelas');
            
        if ($validator->fails()) { Response::backWithErrors($validator->errors(), $_POST); return; }

        $materiList = [];
        $totalJp = 0;

        if (!empty($_POST['materi_pokok'])) {
            foreach ($_POST['materi_pokok'] as $idx => $materi) {
                if (!empty(trim($materi))) {
                    $jp = (int) ($_POST['alokasi_jp'][$idx] ?? $_POST['materi_jp'][$idx] ?? 0);
                    $totalJp += $jp;
                    
                    $matriks = [];
                    for ($b = 1; $b <= 6; $b++) {
                        $matriks[$b] = [];
                        for ($w = 1; $w <= 5; $w++) {
                            $key = "matriks_b{$b}_w{$w}";
                            $val = !empty($_POST[$key][$idx]) ? trim($_POST[$key][$idx]) : '';
                            $matriks[$b][$w] = $val;
                            $matriks["b{$b}_w{$w}"] = $val;
                        }
                    }

                    $materiList[] = [
                        'no' => ($idx + 1),
                        'materi_pokok' => $materi,
                        'tp_materi' => $materi,
                        'alokasi_jp' => $jp,
                        'matriks' => $matriks,
                        'keterangan' => trim($_POST['materi_ket'][$idx] ?? '')
                    ];
                }
            }
        }

        $filePath = $item['file_lampiran'];
        if (!empty($_FILES['file_lampiran']['name'])) {
            $newPath = PerangkatModel::uploadFile($_FILES['file_lampiran'], 'prosem');
            if ($newPath) { $filePath = $newPath; }
        }

        $status = $item['status'];
        if (isset($_POST['ajukan'])) {
            $status = 'diajukan';
        }

        $oldKonten = !empty($item['konten_json']) ? json_decode($item['konten_json'], true) : [];
        $bulanNames = ($_POST['semester'] === 'Genap')
            ? ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni']
            : ['Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        $data = [
            'unit' => $_POST['unit'] ?? $item['unit'] ?? 'SD',
            'judul' => trim($_POST['judul']),
            'tahun_akademik_id' => (int) $_POST['tahun_akademik_id'],
            'semester' => $_POST['semester'],
            'mata_pelajaran' => trim($_POST['mata_pelajaran']),
            'tingkat_kelas' => trim($_POST['tingkat_kelas']),
            'fase' => trim($_POST['fase'] ?? ''),
            'alokasi_waktu' => "{$totalJp} JP",
            'guru_id' => !empty($_POST['guru_id']) ? (int)$_POST['guru_id'] : null,
            'guru_nama' => trim($_POST['guru_nama'] ?? Auth::name() ?? 'Administrator'),
            'guru_nip' => trim($_POST['guru_nip'] ?? ''),
            'konten_json' => [
                'prosem_group_id' => $oldKonten['prosem_group_id'] ?? null,
                'cpatp_id' => $oldKonten['cpatp_id'] ?? null,
                'bulan_names' => $bulanNames,
                'bulan_list' => $bulanNames,
                'materi_list' => $materiList,
                'prosem_rows' => $materiList,
                'total_jp' => $totalJp
            ],
            'file_lampiran' => $filePath,
            'status' => $status
        ];

        PerangkatModel::update($id, $data);

        if (isset($_POST['ajukan']) && $item['status'] !== 'diajukan') {
            PerangkatModel::updateStatus($id, 'diajukan', 'Diajukan untuk peninjauan/verifikasi.', Auth::id(), Auth::name());
        }

        $groupId = $oldKonten['prosem_group_id'] ?? 0;
        $redirectUrl = ($groupId > 0) ? url("kelola-perangkat-pembelajaran/prosem/group/{$groupId}") : url('kelola-perangkat-pembelajaran/prosem');
        Response::withSuccess($redirectUrl, 'Program Semester (Prosem) berhasil diperbarui.');
    }

    public static function detailProsem(int $id): void
    {
        $item = PerangkatModel::getById($id);
        if (!$item) { Response::withError(url('kelola-perangkat-pembelajaran/prosem'), 'Data tidak ditemukan.'); return; }

        $konten = !empty($item['konten_json']) ? json_decode($item['konten_json'], true) : [];
        $logs = PerangkatModel::getLogs($id);

        $groupId = $konten['prosem_group_id'] ?? 0;
        $group = null;
        if ($groupId > 0) {
            $group = PerangkatModel::getById($groupId);
        }

        self::view('prosem/detail', [
            'item' => $item,
            'konten' => $konten,
            'logs' => $logs,
            'group' => $group,
            'can_approve' => PerangkatModel::canApprove()
        ], 'Detail Program Semester (Prosem)', [
            ['label' => 'Perangkat Pembelajaran', 'url' => url('kelola-perangkat-pembelajaran')],
            ['label' => 'Prosem', 'url' => url('kelola-perangkat-pembelajaran/prosem')],
            ['label' => 'Detail']
        ]);
    }

    public static function getUnitProfile(string $unit): array
    {
        require_once BASE_PATH . '/modules/kelola-perangkat-pembelajaran/models/HariEfektifEngine.php';
        $engine = new HariEfektifEngine();
        return $engine->getUnitProfile($unit);
    }

    public static function cetakProsem(int $id): void
    {
        $item = PerangkatModel::getById($id);
        if (!$item) { Response::withError(url('kelola-perangkat-pembelajaran/prosem'), 'Data tidak ditemukan.'); return; }
        
        $konten = !empty($item['konten_json']) ? json_decode($item['konten_json'], true) : [];
        $unitProfile = self::getUnitProfile($item['unit'] ?? 'SD');
        include BASE_PATH . '/modules/kelola-perangkat-pembelajaran/views/prosem/cetak.php';
        exit;
    }

    public static function cetakSemuaProsemGroup(int $id): void
    {
        $group = PerangkatModel::getById($id);
        if (!$group || $group['tipe'] !== 'prosem_group') {
            Response::withError(url('kelola-perangkat-pembelajaran/prosem'), 'Grup Prosem tidak ditemukan.');
            return;
        }

        $db = Database::getInstance();
        $items = $db->findAll("
            SELECT p.*, ta.nama_tahun 
            FROM perangkat_pembelajaran p
            JOIN tahun_akademik ta ON ta.id = p.tahun_akademik_id
            WHERE p.tipe = 'prosem' AND p.unit = ? AND p.tahun_akademik_id = ? AND p.semester = ?
            ORDER BY p.mata_pelajaran ASC, p.tingkat_kelas ASC
        ", [$group['unit'], $group['tahun_akademik_id'], $group['semester']]);

        $unitProfile = self::getUnitProfile($group['unit']);
        include BASE_PATH . '/modules/kelola-perangkat-pembelajaran/views/prosem/cetak_semua.php';
        exit;
    }

    public static function deleteProsem(int $id): void
    {
        if (!CSRF::validate()) { Response::withError(url('kelola-perangkat-pembelajaran/prosem'), 'Token CSRF tidak valid.'); return; }
        
        $item = PerangkatModel::getById($id);
        if (!$item || $item['tipe'] !== 'prosem') {
            Response::withError(url('kelola-perangkat-pembelajaran/prosem'), 'Data tidak ditemukan.');
            return;
        }

        $konten = !empty($item['konten_json']) ? json_decode($item['konten_json'], true) : [];
        $groupId = $konten['prosem_group_id'] ?? 0;

        PerangkatModel::delete($id);
        $redirectUrl = ($groupId > 0) ? url("kelola-perangkat-pembelajaran/prosem/group/{$groupId}") : url('kelola-perangkat-pembelajaran/prosem');
        Response::withSuccess($redirectUrl, 'Dokumen Program Semester berhasil dihapus.');
    }

    // ── 6. RPP / MODUL AJAR ─────────────────────────────────
    public static function rpp(): void
    {
        $filters = self::getCommonFilters();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $filterArgs = [
            'tipe' => 'rpp',
            'unit' => $filters['filter_unit'],
            'tahun_akademik_id' => $filters['filter_ta'],
            'semester' => $filters['filter_semester'],
            'status' => $filters['filter_status'],
            'search' => $filters['search']
        ];

        $items = PerangkatModel::getAll($filterArgs, $limit, $offset);
        $total = PerangkatModel::countAll($filterArgs);
        $totalPages = max(1, ceil($total / $limit));

        self::view('rpp/index', array_merge($filters, [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'can_approve' => PerangkatModel::canApprove()
        ]), 'RPP / Modul Ajar', [
            ['label' => 'Perangkat Pembelajaran', 'url' => url('kelola-perangkat-pembelajaran')],
            ['label' => 'RPP / Modul Ajar']
        ]);
    }

    public static function createRpp(): void
    {
        $filters = self::getCommonFilters();
        self::view('rpp/create', $filters, 'Buat RPP / Modul Ajar', [
            ['label' => 'Perangkat Pembelajaran', 'url' => url('kelola-perangkat-pembelajaran')],
            ['label' => 'RPP / Modul Ajar', 'url' => url('kelola-perangkat-pembelajaran/rpp')],
            ['label' => 'Buat Baru']
        ]);
    }

    public static function storeRpp(): void
    {
        if (!CSRF::validate()) { Response::withError(url('kelola-perangkat-pembelajaran/rpp'), 'Token CSRF tidak valid.'); return; }

        $validator = Validator::make($_POST)
            ->required('judul', 'Judul Modul / Topik')
            ->required('unit', 'Unit Sekolah')
            ->required('tahun_akademik_id', 'Tahun Ajaran')
            ->required('semester', 'Semester')
            ->required('mata_pelajaran', 'Mata Pelajaran')
            ->required('tingkat_kelas', 'Tingkat / Kelas');
            
        if ($validator->fails()) { Response::backWithErrors($validator->errors(), $_POST); return; }

        // Profil pelajar pancasila
        $profilPancasila = $_POST['profil_pancasila'] ?? [];

        $filePath = null;
        if (!empty($_FILES['file_lampiran']['name'])) {
            $filePath = PerangkatModel::uploadFile($_FILES['file_lampiran'], 'rpp');
        }

        $data = [
            'tipe' => 'rpp',
            'unit' => $_POST['unit'] ?? 'SD',
            'judul' => trim($_POST['judul']),
            'tahun_akademik_id' => (int) $_POST['tahun_akademik_id'],
            'semester' => $_POST['semester'],
            'mata_pelajaran' => trim($_POST['mata_pelajaran']),
            'tingkat_kelas' => trim($_POST['tingkat_kelas']),
            'fase' => trim($_POST['fase'] ?? ''),
            'alokasi_waktu' => trim($_POST['alokasi_waktu'] ?? '2 x 45 Menit'),
            'guru_id' => !empty($_POST['guru_id']) ? (int)$_POST['guru_id'] : null,
            'guru_nama' => trim($_POST['guru_nama'] ?? Auth::name() ?? 'Administrator'),
            'guru_nip' => trim($_POST['guru_nip'] ?? ''),
            'konten_json' => [
                'model_pembelajaran' => trim($_POST['model_pembelajaran'] ?? 'Problem Based Learning'),
                'sarana_prasarana' => trim($_POST['sarana_prasarana'] ?? ''),
                'target_peserta' => trim($_POST['target_peserta'] ?? 'Reguler / Umum'),
                'profil_pancasila' => $profilPancasila,
                'tujuan_pembelajaran' => trim($_POST['tujuan_pembelajaran'] ?? ''),
                'pemahaman_bermakna' => trim($_POST['pemahaman_bermakna'] ?? ''),
                'pertanyaan_pemantik' => trim($_POST['pertanyaan_pemantik'] ?? ''),
                'kegiatan_pendahuluan' => trim($_POST['kegiatan_pendahuluan'] ?? ''),
                'kegiatan_inti' => trim($_POST['kegiatan_inti'] ?? ''),
                'kegiatan_penutup' => trim($_POST['kegiatan_penutup'] ?? ''),
                'asesmen_diagnostik' => trim($_POST['asesmen_diagnostik'] ?? ''),
                'asesmen_formatif' => trim($_POST['asesmen_formatif'] ?? ''),
                'asesmen_sumatif' => trim($_POST['asesmen_sumatif'] ?? ''),
                'remedial_pengayaan' => trim($_POST['remedial_pengayaan'] ?? ''),
                'refleksi_guru_siswa' => trim($_POST['refleksi_guru_siswa'] ?? '')
            ],
            'file_lampiran' => $filePath,
            'status' => isset($_POST['ajukan']) ? 'diajukan' : 'draft',
            'created_by' => Auth::id() ?? 1
        ];

        $id = PerangkatModel::create($data);
        if (isset($_POST['ajukan'])) {
            PerangkatModel::updateStatus($id, 'diajukan', 'Pengajuan awal saat pembuatan.', Auth::id(), Auth::name());
        }

        Response::withSuccess(url('kelola-perangkat-pembelajaran/rpp'), 'RPP / Modul Ajar berhasil disimpan.');
    }

    public static function editRpp(int $id): void
    {
        $item = PerangkatModel::getById($id);
        if (!$item || $item['tipe'] !== 'rpp') {
            Response::withError(url('kelola-perangkat-pembelajaran/rpp'), 'Data RPP tidak ditemukan.');
            return;
        }

        $filters = self::getCommonFilters();
        $konten = !empty($item['konten_json']) ? json_decode($item['konten_json'], true) : [];
        $logs = PerangkatModel::getLogs($id);

        self::view('rpp/edit', array_merge($filters, [
            'item' => $item,
            'konten' => $konten,
            'logs' => $logs
        ]), 'Edit RPP / Modul Ajar', [
            ['label' => 'Perangkat Pembelajaran', 'url' => url('kelola-perangkat-pembelajaran')],
            ['label' => 'RPP / Modul Ajar', 'url' => url('kelola-perangkat-pembelajaran/rpp')],
            ['label' => 'Edit']
        ]);
    }

    public static function updateRpp(int $id): void
    {
        if (!CSRF::validate()) { Response::withError(url('kelola-perangkat-pembelajaran/rpp'), 'Token CSRF tidak valid.'); return; }

        $item = PerangkatModel::getById($id);
        if (!$item || $item['tipe'] !== 'rpp') {
            Response::withError(url('kelola-perangkat-pembelajaran/rpp'), 'Data RPP tidak ditemukan.');
            return;
        }

        $validator = Validator::make($_POST)
            ->required('judul', 'Judul Modul / Topik')
            ->required('unit', 'Unit Sekolah')
            ->required('tahun_akademik_id', 'Tahun Ajaran')
            ->required('semester', 'Semester')
            ->required('mata_pelajaran', 'Mata Pelajaran')
            ->required('tingkat_kelas', 'Tingkat / Kelas');
            
        if ($validator->fails()) { Response::backWithErrors($validator->errors(), $_POST); return; }

        $profilPancasila = $_POST['profil_pancasila'] ?? [];

        $filePath = $item['file_lampiran'];
        if (!empty($_FILES['file_lampiran']['name'])) {
            $newPath = PerangkatModel::uploadFile($_FILES['file_lampiran'], 'rpp');
            if ($newPath) { $filePath = $newPath; }
        }

        $status = $item['status'];
        if (isset($_POST['ajukan'])) {
            $status = 'diajukan';
        }

        $data = [
            'unit' => $_POST['unit'] ?? $item['unit'] ?? 'SD',
            'judul' => trim($_POST['judul']),
            'tahun_akademik_id' => (int) $_POST['tahun_akademik_id'],
            'semester' => $_POST['semester'],
            'mata_pelajaran' => trim($_POST['mata_pelajaran']),
            'tingkat_kelas' => trim($_POST['tingkat_kelas']),
            'fase' => trim($_POST['fase'] ?? ''),
            'alokasi_waktu' => trim($_POST['alokasi_waktu'] ?? '2 x 45 Menit'),
            'guru_id' => !empty($_POST['guru_id']) ? (int)$_POST['guru_id'] : null,
            'guru_nama' => trim($_POST['guru_nama'] ?? Auth::name() ?? 'Administrator'),
            'guru_nip' => trim($_POST['guru_nip'] ?? ''),
            'konten_json' => [
                'model_pembelajaran' => trim($_POST['model_pembelajaran'] ?? 'Problem Based Learning'),
                'sarana_prasarana' => trim($_POST['sarana_prasarana'] ?? ''),
                'target_peserta' => trim($_POST['target_peserta'] ?? 'Reguler / Umum'),
                'profil_pancasila' => $profilPancasila,
                'tujuan_pembelajaran' => trim($_POST['tujuan_pembelajaran'] ?? ''),
                'pemahaman_bermakna' => trim($_POST['pemahaman_bermakna'] ?? ''),
                'pertanyaan_pemantik' => trim($_POST['pertanyaan_pemantik'] ?? ''),
                'kegiatan_pendahuluan' => trim($_POST['kegiatan_pendahuluan'] ?? ''),
                'kegiatan_inti' => trim($_POST['kegiatan_inti'] ?? ''),
                'kegiatan_penutup' => trim($_POST['kegiatan_penutup'] ?? ''),
                'asesmen_diagnostik' => trim($_POST['asesmen_diagnostik'] ?? ''),
                'asesmen_formatif' => trim($_POST['asesmen_formatif'] ?? ''),
                'asesmen_sumatif' => trim($_POST['asesmen_sumatif'] ?? ''),
                'remedial_pengayaan' => trim($_POST['remedial_pengayaan'] ?? ''),
                'refleksi_guru_siswa' => trim($_POST['refleksi_guru_siswa'] ?? '')
            ],
            'file_lampiran' => $filePath,
            'status' => $status
        ];

        PerangkatModel::update($id, $data);

        if (isset($_POST['ajukan']) && $item['status'] !== 'diajukan') {
            PerangkatModel::updateStatus($id, 'diajukan', 'Diajukan untuk peninjauan/verifikasi.', Auth::id(), Auth::name());
        }

        Response::withSuccess(url('kelola-perangkat-pembelajaran/rpp'), 'RPP / Modul Ajar berhasil diperbarui.');
    }

    public static function detailRpp(int $id): void
    {
        $item = PerangkatModel::getById($id);
        if (!$item) { Response::withError(url('kelola-perangkat-pembelajaran/rpp'), 'Data tidak ditemukan.'); return; }

        $konten = !empty($item['konten_json']) ? json_decode($item['konten_json'], true) : [];
        $logs = PerangkatModel::getLogs($id);

        self::view('rpp/detail', [
            'item' => $item,
            'konten' => $konten,
            'logs' => $logs,
            'can_approve' => PerangkatModel::canApprove()
        ], 'Detail RPP / Modul Ajar', [
            ['label' => 'Perangkat Pembelajaran', 'url' => url('kelola-perangkat-pembelajaran')],
            ['label' => 'RPP / Modul Ajar', 'url' => url('kelola-perangkat-pembelajaran/rpp')],
            ['label' => 'Detail']
        ]);
    }

    public static function cetakRpp(int $id): void
    {
        $item = PerangkatModel::getById($id);
        if (!$item) { Response::withError(url('kelola-perangkat-pembelajaran/rpp'), 'Data tidak ditemukan.'); return; }
        
        $konten = !empty($item['konten_json']) ? json_decode($item['konten_json'], true) : [];
        include BASE_PATH . '/modules/kelola-perangkat-pembelajaran/views/rpp/cetak.php';
        exit;
    }

    // ── 7. VERIFIKASI & APPROVAL HUB ────────────────────────
    public static function verifikasi(): void
    {
        $filters = self::getCommonFilters();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $tab = trim($_GET['tab'] ?? 'diajukan');
        $filterArgs = [
            'unit' => $filters['filter_unit'],
            'tahun_akademik_id' => $filters['filter_ta'],
            'semester' => $filters['filter_semester'],
            'search' => $filters['search']
        ];

        if ($tab !== 'semua') {
            $filterArgs['status'] = $tab;
        }

        $filterArgs['tipe'] = !empty($_GET['tipe']) ? trim($_GET['tipe']) : 'rpp';

        $items = PerangkatModel::getAll($filterArgs, $limit, $offset);
        $total = PerangkatModel::countAll($filterArgs);
        $totalPages = max(1, ceil($total / $limit));

        $stats = PerangkatModel::getDashboardStats($filters['filter_ta'], $filters['filter_semester'], $filters['filter_unit']);

        self::view('verifikasi/index', array_merge($filters, [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'tab' => $tab,
            'stats' => $stats,
            'can_approve' => PerangkatModel::canApprove()
        ]), 'Pusat Verifikasi RPP / Modul Ajar', [
            ['label' => 'Perangkat Pembelajaran', 'url' => url('kelola-perangkat-pembelajaran')],
            ['label' => 'Pusat Verifikasi RPP']
        ]);
    }

    public static function approve(int $id): void
    {
        if (!CSRF::validate()) { Response::withError(url('kelola-perangkat-pembelajaran/verifikasi'), 'Token CSRF tidak valid.'); return; }
        if (!PerangkatModel::canApprove()) {
            Response::withError(url('kelola-perangkat-pembelajaran'), 'Anda tidak memiliki hak akses untuk menyetujui dokumen ini.');
            return;
        }

        $catatan = trim($_POST['catatan'] ?? 'Dokumen telah diperiksa dan disetujui.');
        PerangkatModel::updateStatus($id, 'disetujui', $catatan, Auth::id(), Auth::name());

        $referer = $_SERVER['HTTP_REFERER'] ?? url('kelola-perangkat-pembelajaran/verifikasi');
        Response::withSuccess($referer, 'Dokumen berhasil disetujui.');
    }

    public static function reject(int $id): void
    {
        if (!CSRF::validate()) { Response::withError(url('kelola-perangkat-pembelajaran/verifikasi'), 'Token CSRF tidak valid.'); return; }
        if (!PerangkatModel::canApprove()) {
            Response::withError(url('kelola-perangkat-pembelajaran'), 'Anda tidak memiliki hak akses untuk menolak dokumen ini.');
            return;
        }

        $catatan = trim($_POST['catatan_revisi'] ?? $_POST['catatan'] ?? '');
        if (empty($catatan)) {
            $catatan = 'Mohon periksa kembali dan lakukan perbaikan sesuai arahan kurikulum.';
        }

        PerangkatModel::updateStatus($id, 'ditolak', $catatan, Auth::id(), Auth::name());

        $referer = $_SERVER['HTTP_REFERER'] ?? url('kelola-perangkat-pembelajaran/verifikasi');
        Response::withWarning($referer, 'Dokumen ditolak / diminta perbaikan.');
    }

    public static function submitReview(int $id): void
    {
        if (!CSRF::validate()) { Response::withError(url('kelola-perangkat-pembelajaran'), 'Token CSRF tidak valid.'); return; }

        $catatan = trim($_POST['catatan'] ?? 'Diajukan untuk peninjauan.');
        PerangkatModel::updateStatus($id, 'diajukan', $catatan, Auth::id(), Auth::name());

        $referer = $_SERVER['HTTP_REFERER'] ?? url('kelola-perangkat-pembelajaran');
        Response::withSuccess($referer, 'Dokumen berhasil diajukan untuk verifikasi.');
    }

    public static function draft(int $id): void
    {
        if (!CSRF::validate()) { Response::withError(url('kelola-perangkat-pembelajaran'), 'Token CSRF tidak valid.'); return; }

        PerangkatModel::updateStatus($id, 'draft', 'Status dikembalikan ke draft.', Auth::id(), Auth::name());

        $referer = $_SERVER['HTTP_REFERER'] ?? url('kelola-perangkat-pembelajaran');
        Response::withSuccess($referer, 'Status dokumen dikembalikan ke draft.');
    }

    public static function delete(int $id): void
    {
        if (!CSRF::validate()) { Response::withError(url('kelola-perangkat-pembelajaran'), 'Token CSRF tidak valid.'); return; }

        $item = PerangkatModel::getById($id);
        if (!$item) {
            Response::withError(url('kelola-perangkat-pembelajaran'), 'Data tidak ditemukan.');
            return;
        }

        // Check permission: only creator or admin/super admin
        if ($item['created_by'] !== Auth::id() && !Auth::isSuperAdmin()) {
            Response::withError(url('kelola-perangkat-pembelajaran'), 'Anda tidak memiliki hak untuk menghapus dokumen ini.');
            return;
        }

        $tipe = $item['tipe'];
        PerangkatModel::delete($id);

        $redirectUrl = url('kelola-perangkat-pembelajaran/' . ($tipe !== 'rpp' ? $tipe : 'rpp'));
        Response::withSuccess($redirectUrl, 'Dokumen berhasil dihapus.');
    }

    // ── 2B. RINCIAN HARI EFEKTIF TERPADU (HEB & HES AUTO-GENERATED) ──
    public static function rincianHariEfektif(): void
    {
        require_once BASE_PATH . '/modules/kelola-perangkat-pembelajaran/models/HariEfektifEngine.php';

        $db = Database::getInstance();
        $unitList = PerangkatModel::getUnitList();
        $taList = PerangkatModel::getTahunAkademikList();

        $selectedUnit = $_GET['unit'] ?? 'SD';
        if (!isset($unitList[$selectedUnit])) {
            $selectedUnit = 'SD';
        }

        $activeTa = !empty($taList) ? $taList[0]['nama_tahun'] : '2026/2027';
        $selectedTaNama = $_GET['tahun_ajaran'] ?? $activeTa;
        $selectedSemester = $_GET['semester'] ?? 'Ganjil';

        // Ambil daftar guru dari tabel pegawai yang memiliki penugasan / ada di unit
        $guruList = $db->findAll("
            SELECT DISTINCT p.id, p.nama, p.gelar 
            FROM pegawai p
            JOIN pegawai_penugasan_mengajar m ON m.pegawai_id = p.id
            ORDER BY p.nama ASC
        ");
        if (empty($guruList)) {
            $guruList = $db->findAll("SELECT id, nama, gelar FROM pegawai WHERE is_active = 1 ORDER BY nama ASC");
        }

        $selectedGuruId = !empty($_GET['guru_id']) ? (int)$_GET['guru_id'] : 0;
        
        // Auto-select guru jika login sebagai guru
        if (!$selectedGuruId && class_exists('Auth') && !Auth::isSuperAdmin()) {
            $userPegawai = $db->find("SELECT id FROM pegawai WHERE email = ? OR nama = ?", [Auth::user()['email'] ?? '', Auth::name() ?? '']);
            if ($userPegawai) {
                $selectedGuruId = (int)$userPegawai['id'];
            }
        }
        if (!$selectedGuruId && !empty($guruList)) {
            $selectedGuruId = (int)$guruList[0]['id'];
        }

        // Ambil daftar penugasan lengkap guru (kelas + mapel)
        $penugasanList = [];
        $kelasList = [];
        $mapelList = [];
        if ($selectedGuruId) {
            $penugasanRows = $db->findAll("
                SELECT DISTINCT nama_kelas, mata_pelajaran, jumlah_jp
                FROM pegawai_penugasan_mengajar 
                WHERE pegawai_id = ? AND nama_kelas IS NOT NULL AND nama_kelas != ''
                ORDER BY nama_kelas ASC, mata_pelajaran ASC
            ", [$selectedGuruId]);

            foreach ($penugasanRows as $pr) {
                $penugasanList[] = $pr;
                if (!in_array($pr['nama_kelas'], $kelasList)) $kelasList[] = $pr['nama_kelas'];
                if (!in_array($pr['mata_pelajaran'], $mapelList)) $mapelList[] = $pr['mata_pelajaran'];
            }
        }

        if (empty($kelasList)) {
            $kelasListRows = $db->findAll("SELECT DISTINCT kelas FROM siswa WHERE jenjang = ? ORDER BY kelas ASC", [$selectedUnit]);
            foreach ($kelasListRows as $kr) {
                if (!empty($kr['kelas'])) $kelasList[] = $kr['kelas'];
            }
        }

        $selectedKelas = $_GET['kelas'] ?? '';
        $selectedMapel = $_GET['mapel'] ?? '';

        $resultsList = [];
        $engine = new HariEfektifEngine();

        if ($selectedGuruId) {
            if (!empty($penugasanList)) {
                foreach ($penugasanList as $p) {
                    if (!empty($selectedKelas) && $p['nama_kelas'] !== $selectedKelas) {
                        continue;
                    }
                    if (!empty($selectedMapel) && $p['mata_pelajaran'] !== $selectedMapel) {
                        continue;
                    }

                    $resultsList[] = $engine->hitung(
                        $selectedUnit,
                        $selectedTaNama,
                        $selectedSemester,
                        $selectedGuruId,
                        $p['nama_kelas'],
                        $p['mata_pelajaran']
                    );
                }
            }

            // Fallback jika tidak ada di penugasanList atau penugasan kosong
            if (empty($resultsList)) {
                $fallbackKelas = !empty($selectedKelas) ? $selectedKelas : ($kelasList[0] ?? 'Kelas 1');
                $fallbackMapel = !empty($selectedMapel) ? $selectedMapel : ($mapelList[0] ?? 'Mata Pelajaran');
                $resultsList[] = $engine->hitung(
                    $selectedUnit,
                    $selectedTaNama,
                    $selectedSemester,
                    $selectedGuruId,
                    $fallbackKelas,
                    $fallbackMapel
                );
            }
        }

        self::view('heb_hes/index', [
            'unitList' => $unitList,
            'taList' => $taList,
            'guruList' => $guruList,
            'penugasanList' => $penugasanList,
            'kelasList' => $kelasList,
            'mapelList' => $mapelList,
            'selectedUnit' => $selectedUnit,
            'selectedTaNama' => $selectedTaNama,
            'selectedSemester' => $selectedSemester,
            'selectedGuruId' => $selectedGuruId,
            'selectedKelas' => $selectedKelas,
            'selectedMapel' => $selectedMapel,
            'resultsList' => $resultsList
        ], 'Rincian Hari Efektif (HEB & HES)', [
            ['label' => 'Perangkat Pembelajaran', 'url' => url('kelola-perangkat-pembelajaran')],
            ['label' => 'Rincian Hari Efektif (HEB & HES)']
        ]);
    }

    public static function cetakRincianHariEfektif(): void
    {
        require_once BASE_PATH . '/modules/kelola-perangkat-pembelajaran/models/HariEfektifEngine.php';

        $db = Database::getInstance();
        $unit = $_GET['unit'] ?? 'SD';
        $tahunAjaran = $_GET['tahun_ajaran'] ?? '2026/2027';
        $semester = $_GET['semester'] ?? 'Ganjil';
        $guruId = (int)($_GET['guru_id'] ?? 0);
        $kelas = $_GET['kelas'] ?? '';
        $mapel = $_GET['mapel'] ?? '';

        $engine = new HariEfektifEngine();
        $resultsList = [];

        if (!empty($kelas) && !empty($mapel)) {
            $resultsList[] = $engine->hitung($unit, $tahunAjaran, $semester, $guruId, $kelas, $mapel);
        } else {
            // Ambil semua penugasan guru ini
            $penugasanRows = $db->findAll("
                SELECT DISTINCT nama_kelas, mata_pelajaran
                FROM pegawai_penugasan_mengajar 
                WHERE pegawai_id = ? AND nama_kelas IS NOT NULL AND nama_kelas != ''
                ORDER BY nama_kelas ASC, mata_pelajaran ASC
            ", [$guruId]);

            if (!empty($penugasanRows)) {
                foreach ($penugasanRows as $pr) {
                    if (!empty($kelas) && $pr['nama_kelas'] !== $kelas) continue;
                    if (!empty($mapel) && $pr['mata_pelajaran'] !== $mapel) continue;
                    $resultsList[] = $engine->hitung($unit, $tahunAjaran, $semester, $guruId, $pr['nama_kelas'], $pr['mata_pelajaran']);
                }
            } else {
                $resultsList[] = $engine->hitung($unit, $tahunAjaran, $semester, $guruId, $kelas ?: 'Kelas 1', $mapel ?: 'Umum');
            }
        }

        include BASE_PATH . '/modules/kelola-perangkat-pembelajaran/views/heb_hes/cetak.php';
        exit;
    }
}
