<?php
/**
 * Jadwal Controller - Portal BIP
 * Modul Kelola Perangkat Pembelajaran
 */

require_once __DIR__ . '/../models/JadwalGeneratorEngine.php';

class JadwalController
{
    public static function index(): void
    {
        $pageTitle = 'Jadwal Pelajaran & KBM';
        $breadcrumbs = [
            ['label' => 'Perangkat Pembelajaran', 'url' => url('kelola-perangkat-pembelajaran')],
            ['label' => 'Jadwal Pelajaran']
        ];

        $db = Database::getInstance();
        $grupList = $db->findAll("
            SELECT g.*, 
                   COUNT(DISTINCT j.nama_kelas) as real_kelas_count,
                   COUNT(j.id) as real_slots_count,
                   COUNT(DISTINCT j.pegawai_id) as real_guru_count
            FROM jadwal_grup g
            LEFT JOIN jadwal_pelajaran j ON g.id = j.grup_id
            GROUP BY g.id
            ORDER BY g.is_active DESC, g.id DESC
        ");

        // KPI aggregates
        $activeGrup = $db->find("SELECT * FROM jadwal_grup WHERE is_active = 1 LIMIT 1");
        $totalGrup = count($grupList);
        $totalSlotAktif = 0;
        $totalKelasAktif = 0;
        if ($activeGrup) {
            $totalSlotAktif = $db->find("SELECT COUNT(*) as total FROM jadwal_pelajaran WHERE grup_id = ?", [$activeGrup['id']])['total'] ?? 0;
            $totalKelasAktif = $db->find("SELECT COUNT(DISTINCT nama_kelas) as total FROM jadwal_pelajaran WHERE grup_id = ?", [$activeGrup['id']])['total'] ?? 0;
        }

        ob_start();
        include MODULES_PATH . '/kelola-perangkat-pembelajaran/views/jadwal/index.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-perangkat-pembelajaran/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function create(): void
    {
        $pageTitle = 'Buat Grup Jadwal Baru';
        $breadcrumbs = [
            ['label' => 'Perangkat Pembelajaran', 'url' => url('kelola-perangkat-pembelajaran')],
            ['label' => 'Jadwal Pelajaran', 'url' => url('kelola-perangkat-pembelajaran/jadwal')],
            ['label' => 'Buat Grup Jadwal']
        ];

        $db = Database::getInstance();
        $penugasanGrupList = $db->findAll("SELECT id, nama_grup, no_sk, semester FROM penugasan_grup ORDER BY id DESC");

        ob_start();
        include MODULES_PATH . '/kelola-perangkat-pembelajaran/views/jadwal/create.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-perangkat-pembelajaran/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function store(): void
    {
        if (!CSRF::validate()) {
            Response::withError(url('kelola-perangkat-pembelajaran/jadwal/create'), 'Token keamanan tidak valid.');
            return;
        }

        $namaGrup = trim($_POST['nama_grup'] ?? '');
        if (empty($namaGrup)) {
            Response::withError(url('kelola-perangkat-pembelajaran/jadwal/create'), 'Nama grup jadwal wajib diisi.');
            return;
        }

        $db = Database::getInstance();
        $tahunAjaran = trim($_POST['tahun_ajaran'] ?? '2026/2027');
        $semester = trim($_POST['semester'] ?? 'Ganjil');
        $jenjang = trim($_POST['jenjang'] ?? 'SD');
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $penugasanGrupId = !empty($_POST['penugasan_grup_id']) ? intval($_POST['penugasan_grup_id']) : null;
        $durasiJp = max(20, min(90, intval($_POST['durasi_jp_menit'] ?? 35)));
        $jamMulai = trim($_POST['jam_mulai_kbm'] ?? '07:15:00');

        $pdo = $db->getConnection();

        // If set active, deactivate others
        if ($isActive) {
            $pdo->exec("UPDATE jadwal_grup SET is_active = 0");
        }

        $sql = "
            INSERT INTO jadwal_grup (nama_grup, tahun_ajaran, semester, jenjang, is_active, penugasan_grup_id, keterangan)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $namaGrup,
            $tahunAjaran,
            $semester,
            $jenjang,
            $isActive,
            $penugasanGrupId,
            trim($_POST['keterangan'] ?? '')
        ]);

        $grupId = (int)$pdo->lastInsertId();

        // Inisialisasi Slot Waktu Default berdasarkan durasi JP
        $engine = new JadwalGeneratorEngine($grupId);
        $engine->inisialisasiSlotWaktuDefault($jenjang, $durasiJp, $jamMulai);

        Response::withSuccess(url('kelola-perangkat-pembelajaran/jadwal/generate/' . $grupId), "Grup jadwal '{$namaGrup}' berhasil dibuat dan slot jam pelajaran telah diinisialisasi.");
    }

    public static function edit(int $id): void
    {
        $db = Database::getInstance();
        $grup = $db->find("SELECT * FROM jadwal_grup WHERE id = ?", [$id]);
        if (!$grup) {
            Response::withError(url('kelola-perangkat-pembelajaran/jadwal'), 'Data grup jadwal tidak ditemukan.');
            return;
        }

        $pageTitle = 'Edit Grup Jadwal: ' . $grup['nama_grup'];
        $breadcrumbs = [
            ['label' => 'Perangkat Pembelajaran', 'url' => url('kelola-perangkat-pembelajaran')],
            ['label' => 'Jadwal Pelajaran', 'url' => url('kelola-perangkat-pembelajaran/jadwal')],
            ['label' => 'Edit Grup']
        ];

        $penugasanGrupList = $db->findAll("SELECT id, nama_grup, no_sk, semester FROM penugasan_grup ORDER BY id DESC");

        ob_start();
        include MODULES_PATH . '/kelola-perangkat-pembelajaran/views/jadwal/edit.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-perangkat-pembelajaran/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function update(int $id): void
    {
        if (!CSRF::validate()) {
            Response::withError(url("kelola-perangkat-pembelajaran/jadwal/edit/{$id}"), 'Token keamanan tidak valid.');
            return;
        }

        $namaGrup = trim($_POST['nama_grup'] ?? '');
        if (empty($namaGrup)) {
            Response::withError(url("kelola-perangkat-pembelajaran/jadwal/edit/{$id}"), 'Nama grup jadwal wajib diisi.');
            return;
        }

        $db = Database::getInstance();
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $pdo = $db->getConnection();

        if ($isActive) {
            $pdo->prepare("UPDATE jadwal_grup SET is_active = 0 WHERE id != ?")->execute([$id]);
        }

        $sql = "
            UPDATE jadwal_grup SET
                nama_grup = ?, tahun_ajaran = ?, semester = ?, jenjang = ?,
                is_active = ?, penugasan_grup_id = ?, keterangan = ?
            WHERE id = ?
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $namaGrup,
            trim($_POST['tahun_ajaran'] ?? '2026/2027'),
            trim($_POST['semester'] ?? 'Ganjil'),
            trim($_POST['jenjang'] ?? 'SD'),
            $isActive,
            !empty($_POST['penugasan_grup_id']) ? intval($_POST['penugasan_grup_id']) : null,
            trim($_POST['keterangan'] ?? ''),
            $id
        ]);

        Response::withSuccess(url('kelola-perangkat-pembelajaran/jadwal'), "Data grup jadwal '{$namaGrup}' berhasil diperbarui.");
    }

    public static function delete(int $id): void
    {
        if (!CSRF::validate()) {
            Response::withError(url('kelola-perangkat-pembelajaran/jadwal'), 'Token keamanan tidak valid.');
            return;
        }

        $db = Database::getInstance();
        $pdo = $db->getConnection();

        $pdo->prepare("DELETE FROM jadwal_pelajaran WHERE grup_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM jadwal_slot_waktu WHERE grup_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM jadwal_pengaturan_jp WHERE grup_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM jadwal_grup WHERE id = ?")->execute([$id]);

        Response::withSuccess(url('kelola-perangkat-pembelajaran/jadwal'), 'Grup jadwal berhasil dihapus.');
    }

    public static function setActive(int $id): void
    {
        if (!CSRF::validate()) {
            Response::withError(url('kelola-perangkat-pembelajaran/jadwal'), 'Token keamanan tidak valid.');
            return;
        }

        $db = Database::getInstance();
        $pdo = $db->getConnection();

        $pdo->exec("UPDATE jadwal_grup SET is_active = 0");
        $pdo->prepare("UPDATE jadwal_grup SET is_active = 1 WHERE id = ?")->execute([$id]);

        $grup = $db->find("SELECT nama_grup FROM jadwal_grup WHERE id = ?", [$id]);
        $nama = $grup['nama_grup'] ?? 'Jadwal';

        Response::withSuccess(url('kelola-perangkat-pembelajaran/jadwal'), "Jadwal '{$nama}' berhasil diaktifkan dan kini berlaku resmi untuk seluruh sistem.");
    }

    public static function pengaturanJp(int $id): void
    {
        $db = Database::getInstance();
        $grup = $db->find("SELECT * FROM jadwal_grup WHERE id = ?", [$id]);
        if (!$grup) {
            Response::withError(url('kelola-perangkat-pembelajaran/jadwal'), 'Data grup jadwal tidak ditemukan.');
            return;
        }

        $pageTitle = 'Pengaturan JP & Jam Rutin: ' . $grup['nama_grup'];
        $breadcrumbs = [
            ['label' => 'Perangkat Pembelajaran', 'url' => url('kelola-perangkat-pembelajaran')],
            ['label' => 'Jadwal Pelajaran', 'url' => url('kelola-perangkat-pembelajaran/jadwal')],
            ['label' => 'Pengaturan JP & Jam']
        ];

        $pengaturanJp = $db->find("SELECT * FROM jadwal_pengaturan_jp WHERE grup_id = ? ORDER BY id DESC LIMIT 1", [$id]);
        $slotList = $db->findAll("
            SELECT * FROM jadwal_slot_waktu 
            WHERE grup_id = ? 
            ORDER BY FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'), jam_mulai ASC, urutan ASC
        ", [$id]);

        // Group slots by day
        $slotsByDay = [];
        foreach ($slotList as $s) {
            $slotsByDay[$s['hari']][] = $s;
        }

        ob_start();
        include MODULES_PATH . '/kelola-perangkat-pembelajaran/views/jadwal/pengaturan_jp.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-perangkat-pembelajaran/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function simpanPengaturanJp(int $id): void
    {
        if (!CSRF::validate()) {
            Response::withError(url("kelola-perangkat-pembelajaran/jadwal/pengaturan-jp/{$id}"), 'Token keamanan tidak valid.');
            return;
        }

        $durasiJp = max(15, min(120, intval($_POST['durasi_jp_menit'] ?? 35)));
        $jamMulai = trim($_POST['jam_mulai_kbm'] ?? '07:00:00');
        $jamSelesai = trim($_POST['jam_selesai_kbm'] ?? '16:00:00');
        $jenjang = trim($_POST['jenjang'] ?? 'SD');

        $engine = new JadwalGeneratorEngine($id);
        $engine->inisialisasiSlotWaktuDefault($jenjang, $durasiJp, $jamMulai, $jamSelesai);

        Response::withSuccess(url("kelola-perangkat-pembelajaran/jadwal/pengaturan-jp/{$id}"), 'Slot jam pelajaran murni s.d. jam 16.00 berhasil dihitung dan diperbarui.');
    }

    public static function tambahSlotKhusus(int $id): void
    {
        if (!CSRF::validate()) {
            Response::withError(url("kelola-perangkat-pembelajaran/jadwal/pengaturan-jp/{$id}"), 'Token keamanan tidak valid.');
            return;
        }

        $db = Database::getInstance();
        $grup = $db->find("SELECT * FROM jadwal_grup WHERE id = ?", [$id]);
        if (!$grup) {
            Response::withError(url('kelola-perangkat-pembelajaran/jadwal'), 'Grup tidak ditemukan.');
            return;
        }

        $hari = trim($_POST['hari'] ?? 'Senin');
        $jenisSlot = trim($_POST['jenis_slot'] ?? 'kegiatan_khusus');
        $labelSlot = trim($_POST['label_slot'] ?? '');
        $jamMulai = trim($_POST['jam_mulai'] ?? '07:00:00');
        $jamSelesai = trim($_POST['jam_selesai'] ?? '07:45:00');
        $jamKe = ($jenisSlot === 'kbm') ? max(1, intval($_POST['jam_ke'] ?? 1)) : 0;
        $jenjang = $grup['jenjang'] ?? 'SD';

        if (empty($labelSlot)) {
            $labelSlot = ucfirst($jenisSlot);
        }

        $pdo = $db->getConnection();
        $pdo->prepare("
            INSERT INTO jadwal_slot_waktu (grup_id, jenjang, hari, jam_ke, jam_mulai, jam_selesai, jenis_slot, label_slot, urutan)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 99)
        ")->execute([$id, $jenjang, $hari, $jamKe, $jamMulai, $jamSelesai, $jenisSlot, $labelSlot]);

        // Re-generate & susun ulang jadwal hari ini agar agenda masuk di jamnya dan JP bergeser runtut
        $engine = new JadwalGeneratorEngine($id);
        $engine->sinkronisasiSlotHarian($hari);

        Response::withSuccess(url("kelola-perangkat-pembelajaran/jadwal/pengaturan-jp/{$id}"), "Agenda '{$labelSlot}' ({$hari} {$jamMulai} - {$jamSelesai}) berhasil disisipkan dan jadwal KBM otomatis dihitung ulang.");
    }

    public static function hapusSlotWaktu(int $id, int $slotId): void
    {
        if (!CSRF::validate()) {
            Response::withError(url("kelola-perangkat-pembelajaran/jadwal/pengaturan-jp/{$id}"), 'Token keamanan tidak valid.');
            return;
        }

        $db = Database::getInstance();
        $slot = $db->find("SELECT * FROM jadwal_slot_waktu WHERE id = ? AND grup_id = ?", [$slotId, $id]);
        if ($slot) {
            $hari = $slot['hari'];
            $pdo = $db->getConnection();
            $pdo->prepare("DELETE FROM jadwal_slot_waktu WHERE id = ? AND grup_id = ?")->execute([$slotId, $id]);

            $engine = new JadwalGeneratorEngine($id);
            $engine->sinkronisasiSlotHarian($hari);
        }

        Response::withSuccess(url("kelola-perangkat-pembelajaran/jadwal/pengaturan-jp/{$id}"), 'Slot waktu berhasil dihapus dan jadwal dihitung ulang.');
    }

    public static function editSlotWaktu(int $id, int $slotId): void
    {
        if (!CSRF::validate()) {
            Response::withError(url("kelola-perangkat-pembelajaran/jadwal/pengaturan-jp/{$id}"), 'Token keamanan tidak valid.');
            return;
        }

        $db = Database::getInstance();
        $slot = $db->find("SELECT * FROM jadwal_slot_waktu WHERE id = ? AND grup_id = ?", [$slotId, $id]);
        if ($slot) {
            $hari = $slot['hari'];
            $labelSlot = trim($_POST['label_slot'] ?? '');
            $jamMulai = trim($_POST['jam_mulai'] ?? '07:00:00');
            $jamSelesai = trim($_POST['jam_selesai'] ?? '07:45:00');
            $jenisSlot = trim($_POST['jenis_slot'] ?? 'kbm');
            $jamKe = ($jenisSlot === 'kbm') ? max(1, intval($_POST['jam_ke'] ?? 1)) : 0;

            $pdo = $db->getConnection();
            $pdo->prepare("
                UPDATE jadwal_slot_waktu SET
                    label_slot = ?, jam_mulai = ?, jam_selesai = ?, jenis_slot = ?, jam_ke = ?
                WHERE id = ? AND grup_id = ?
            ")->execute([$labelSlot, $jamMulai, $jamSelesai, $jenisSlot, $jamKe, $slotId, $id]);

            $engine = new JadwalGeneratorEngine($id);
            $engine->sinkronisasiSlotHarian($hari);
        }

        Response::withSuccess(url("kelola-perangkat-pembelajaran/jadwal/pengaturan-jp/{$id}"), 'Slot waktu berhasil diperbarui dan jadwal dihitung ulang.');
    }

    public static function syncHari(int $id, string $hari): void
    {
        if (!CSRF::validate()) {
            Response::withError(url("kelola-perangkat-pembelajaran/jadwal/pengaturan-jp/{$id}"), 'Token keamanan tidak valid.');
            return;
        }

        $engine = new JadwalGeneratorEngine($id);
        $engine->sinkronisasiSlotHarian($hari);

        Response::withSuccess(url("kelola-perangkat-pembelajaran/jadwal/pengaturan-jp/{$id}"), "Slot waktu hari {$hari} berhasil di-sinkronisasi dan diurutkan secara presisi.");
    }

    public static function generate(int $id): void
    {
        $db = Database::getInstance();
        $grup = $db->find("SELECT * FROM jadwal_grup WHERE id = ?", [$id]);
        if (!$grup) {
            Response::withError(url('kelola-perangkat-pembelajaran/jadwal'), 'Data grup jadwal tidak ditemukan.');
            return;
        }

        $pageTitle = 'Generator Jadwal Cerdas: ' . $grup['nama_grup'];
        $breadcrumbs = [
            ['label' => 'Perangkat Pembelajaran', 'url' => url('kelola-perangkat-pembelajaran')],
            ['label' => 'Jadwal Pelajaran', 'url' => url('kelola-perangkat-pembelajaran/jadwal')],
            ['label' => 'Auto-Generator']
        ];

        // Ambil daftar kelas dari DATA SISWA berdasarkan Unit
        $targetUnit = $grup['jenjang'] ?? 'SD';
        if ($targetUnit === 'SEMUA') {
            $unitKelasSiswa = $db->findAll("SELECT DISTINCT kelas FROM siswa WHERE kelas IS NOT NULL AND kelas != '' ORDER BY kelas ASC");
        } else {
            $unitKelasSiswa = $db->findAll("SELECT DISTINCT kelas FROM siswa WHERE jenjang = ? AND kelas IS NOT NULL AND kelas != '' ORDER BY kelas ASC", [$targetUnit]);
        }
        $validClassLookups = [];
        foreach ($unitKelasSiswa as $sc) {
            $rawK = trim($sc['kelas']);
            $validClassLookups[] = strtolower($rawK);
            $validClassLookups[] = strtolower("kelas " . $rawK);
        }

        // Fetch teaching assignment stats
        $wherePenugasan = '1=1';
        $paramsPenugasan = [];
        if (!empty($grup['penugasan_grup_id'])) {
            $wherePenugasan .= ' AND m.grup_id = ?';
            $paramsPenugasan[] = $grup['penugasan_grup_id'];
        }

        $rawTeachingData = $db->findAll("
            SELECT 
                m.*, p.nama as nama_guru 
            FROM pegawai_penugasan_mengajar m
            JOIN pegawai p ON m.pegawai_id = p.id
            WHERE {$wherePenugasan} AND m.jumlah_jp > 0 AND m.nama_kelas IS NOT NULL AND m.nama_kelas != ''
            ORDER BY m.nama_kelas ASC, m.mata_pelajaran ASC
        ", $paramsPenugasan);

        $teachingData = [];
        if (!empty($validClassLookups)) {
            foreach ($rawTeachingData as $t) {
                if (in_array(strtolower(trim($t['nama_kelas'])), $validClassLookups)) {
                    $teachingData[] = $t;
                }
            }
            if (empty($teachingData) && !empty($rawTeachingData)) {
                $teachingData = $rawTeachingData;
            }
        } else {
            $teachingData = $rawTeachingData;
        }

        $totalJpPenugasan = array_sum(array_column($teachingData, 'jumlah_jp'));
        $totalGuruPenugasan = count(array_unique(array_column($teachingData, 'pegawai_id')));
        $totalKelasPenugasan = count(array_unique(array_column($teachingData, 'nama_kelas')));

        // Slot availability
        $slotCount = $db->find("SELECT COUNT(*) as total FROM jadwal_slot_waktu WHERE grup_id = ? AND jenis_slot = 'kbm'", [$id])['total'] ?? 0;

        ob_start();
        include MODULES_PATH . '/kelola-perangkat-pembelajaran/views/jadwal/generate.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-perangkat-pembelajaran/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function runGenerate(int $id): void
    {
        if (!CSRF::validate()) {
            Response::withError(url("kelola-perangkat-pembelajaran/jadwal/generate/{$id}"), 'Token keamanan tidak valid.');
            return;
        }

        $db = Database::getInstance();
        $grup = $db->find("SELECT * FROM jadwal_grup WHERE id = ?", [$id]);
        if (!$grup) {
            Response::withError(url('kelola-perangkat-pembelajaran/jadwal'), 'Data grup jadwal tidak ditemukan.');
            return;
        }

        $engine = new JadwalGeneratorEngine($id, [
            'penugasan_grup_id' => $grup['penugasan_grup_id'] ?? null,
            'max_block_length' => intval($_POST['max_block_length'] ?? 2),
            'allow_saturday' => isset($_POST['allow_saturday']) ? true : false,
            'clear_existing' => true
        ]);

        $result = $engine->generate();

        if ($result['success']) {
            Response::withSuccess(url("kelola-perangkat-pembelajaran/jadwal/matriks/{$id}"), "Generator Sukses! {$result['total_scheduled_slots']} slot mata pelajaran berhasil dijadwalkan tanpa ada bentrok (0 konflik).");
        } else {
            Response::withError(url("kelola-perangkat-pembelajaran/jadwal/generate/{$id}"), $result['message']);
        }
    }

    public static function matriks(int $id): void
    {
        $db = Database::getInstance();
        $grup = $db->find("SELECT * FROM jadwal_grup WHERE id = ?", [$id]);
        if (!$grup) {
            Response::withError(url('kelola-perangkat-pembelajaran/jadwal'), 'Data grup jadwal tidak ditemukan.');
            return;
        }

        $pageTitle = 'Matriks Jadwal Pelajaran: ' . $grup['nama_grup'];
        $breadcrumbs = [
            ['label' => 'Perangkat Pembelajaran', 'url' => url('kelola-perangkat-pembelajaran')],
            ['label' => 'Jadwal Pelajaran', 'url' => url('kelola-perangkat-pembelajaran/jadwal')],
            ['label' => 'Matriks Jadwal']
        ];

        // Mode: 'kelas' atau 'guru'
        $mode = trim($_GET['mode'] ?? 'kelas');
        $filterKelas = trim($_GET['kelas'] ?? '');
        $filterGuru = intval($_GET['guru_id'] ?? 0);

        // List of all classes with schedule
        $kelasList = $db->findAll("
            SELECT DISTINCT nama_kelas FROM jadwal_pelajaran WHERE grup_id = ? ORDER BY nama_kelas ASC
        ", [$id]);

        if (empty($filterKelas) && !empty($kelasList)) {
            $filterKelas = $kelasList[0]['nama_kelas'];
        }

        // List of all teachers with schedule
        $guruList = $db->findAll("
            SELECT DISTINCT p.id, p.nama 
            FROM jadwal_pelajaran j 
            JOIN pegawai p ON j.pegawai_id = p.id 
            WHERE j.grup_id = ? 
            ORDER BY p.nama ASC
        ", [$id]);

        if (empty($filterGuru) && !empty($guruList)) {
            $filterGuru = (int)$guruList[0]['id'];
        }

        // Master Slots for grid headers
        $slotList = $db->findAll("
            SELECT * FROM jadwal_slot_waktu 
            WHERE grup_id = ? 
            ORDER BY FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'), urutan ASC
        ", [$id]);

        $slotsByDay = [];
        $maxJp = 0;
        foreach ($slotList as $s) {
            $slotsByDay[$s['hari']][] = $s;
            if ($s['jenis_slot'] === 'kbm' && $s['jam_ke'] > $maxJp) {
                $maxJp = (int)$s['jam_ke'];
            }
        }

        // Fetch Schedule Items
        $scheduleItems = [];
        if ($mode === 'kelas' && !empty($filterKelas)) {
            $rawSchedule = $db->findAll("
                SELECT * FROM jadwal_pelajaran 
                WHERE grup_id = ? AND nama_kelas = ?
            ", [$id, $filterKelas]);
            foreach ($rawSchedule as $r) {
                $scheduleItems[$r['hari']][$r['jam_ke']] = $r;
            }
        } elseif ($mode === 'guru' && !empty($filterGuru)) {
            $rawSchedule = $db->findAll("
                SELECT * FROM jadwal_pelajaran 
                WHERE grup_id = ? AND pegawai_id = ?
            ", [$id, $filterGuru]);
            foreach ($rawSchedule as $r) {
                $scheduleItems[$r['hari']][$r['jam_ke']] = $r;
            }
        }

        ob_start();
        include MODULES_PATH . '/kelola-perangkat-pembelajaran/views/jadwal/matriks.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-perangkat-pembelajaran/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function cetakKelas(int $id): void
    {
        $db = Database::getInstance();
        $grup = $db->find("SELECT * FROM jadwal_grup WHERE id = ?", [$id]);
        if (!$grup) {
            Response::withError(url('kelola-perangkat-pembelajaran/jadwal'), 'Data tidak ditemukan.');
            return;
        }

        $namaKelas = trim($_GET['kelas'] ?? '');
        if (empty($namaKelas)) {
            $first = $db->find("SELECT nama_kelas FROM jadwal_pelajaran WHERE grup_id = ? LIMIT 1", [$id]);
            $namaKelas = $first['nama_kelas'] ?? '';
        }

        $slotList = $db->findAll("
            SELECT * FROM jadwal_slot_waktu 
            WHERE grup_id = ? 
            ORDER BY FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'), urutan ASC
        ", [$id]);

        $slotsByDay = [];
        $maxJp = 0;
        foreach ($slotList as $s) {
            $slotsByDay[$s['hari']][] = $s;
            if ($s['jenis_slot'] === 'kbm' && $s['jam_ke'] > $maxJp) {
                $maxJp = (int)$s['jam_ke'];
            }
        }

        $rawSchedule = $db->findAll("
            SELECT * FROM jadwal_pelajaran 
            WHERE grup_id = ? AND nama_kelas = ?
        ", [$id, $namaKelas]);

        $scheduleItems = [];
        foreach ($rawSchedule as $r) {
            $scheduleItems[$r['hari']][$r['jam_ke']] = $r;
        }

        include MODULES_PATH . '/kelola-perangkat-pembelajaran/views/jadwal/cetak_kelas.php';
        exit;
    }

    public static function cetakGuru(int $id): void
    {
        $db = Database::getInstance();
        $grup = $db->find("SELECT * FROM jadwal_grup WHERE id = ?", [$id]);
        if (!$grup) {
            Response::withError(url('kelola-perangkat-pembelajaran/jadwal'), 'Data tidak ditemukan.');
            return;
        }

        $pegawaiId = intval($_GET['guru_id'] ?? 0);
        $guru = $db->find("SELECT * FROM pegawai WHERE id = ?", [$pegawaiId]);
        if (!$guru) {
            Response::withError(url("kelola-perangkat-pembelajaran/jadwal/matriks/{$id}"), 'Guru tidak ditemukan.');
            return;
        }

        $slotList = $db->findAll("
            SELECT * FROM jadwal_slot_waktu 
            WHERE grup_id = ? 
            ORDER BY FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'), urutan ASC
        ", [$id]);

        $slotsByDay = [];
        $maxJp = 0;
        foreach ($slotList as $s) {
            $slotsByDay[$s['hari']][] = $s;
            if ($s['jenis_slot'] === 'kbm' && $s['jam_ke'] > $maxJp) {
                $maxJp = (int)$s['jam_ke'];
            }
        }

        $rawSchedule = $db->findAll("
            SELECT * FROM jadwal_pelajaran 
            WHERE grup_id = ? AND pegawai_id = ?
        ", [$id, $pegawaiId]);

        $scheduleItems = [];
        foreach ($rawSchedule as $r) {
            $scheduleItems[$r['hari']][$r['jam_ke']] = $r;
        }

        include MODULES_PATH . '/kelola-perangkat-pembelajaran/views/jadwal/cetak_guru.php';
        exit;
    }

    public static function export(int $id): void
    {
        $db = Database::getInstance();
        $grup = $db->find("SELECT * FROM jadwal_grup WHERE id = ?", [$id]);
        if (!$grup) {
            Response::withError(url('kelola-perangkat-pembelajaran/jadwal'), 'Data tidak ditemukan.');
            return;
        }

        $rows = $db->findAll("
            SELECT * FROM jadwal_pelajaran 
            WHERE grup_id = ? 
            ORDER BY nama_kelas ASC, FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'), jam_ke ASC
        ", [$id]);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=jadwal_pelajaran_' . date('Ymd_His') . '.csv');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, [
            'Nama Grup', 'Tahun Ajaran', 'Semester', 'Kelas', 'Hari', 'Jam Ke', 'Jam Mulai', 'Jam Selesai', 'Mata Pelajaran', 'Nama Guru', 'Ruangan'
        ]);

        foreach ($rows as $r) {
            fputcsv($output, [
                $grup['nama_grup'],
                $grup['tahun_ajaran'],
                $grup['semester'],
                $r['nama_kelas'],
                $r['hari'],
                $r['jam_ke'],
                $r['jam_mulai'],
                $r['jam_selesai'],
                $r['mata_pelajaran'],
                $r['nama_guru'],
                $r['ruangan'] ?? ''
            ]);
        }
        fclose($output);
        exit;
    }
}
