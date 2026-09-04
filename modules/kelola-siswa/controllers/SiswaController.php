<?php
/**
 * Siswa Controller - Portal BIP
 * Comprehensive Student Management CRUD with 50+ Dapodik/BIP Fields
 */

class SiswaController
{
    public static function index(): void
    {
        $pageTitle = 'Kelola Data Siswa';
        $breadcrumbs = [['label' => 'Kelola Siswa']];
        
        $db = Database::getInstance();
        
        // Get Tahun Akademik
        $tahunAkademikList = $db->findAll("SELECT id, nama_tahun, is_active FROM tahun_akademik ORDER BY tanggal_mulai DESC");
        $taAktif = $db->query("SELECT id FROM tahun_akademik WHERE is_active = 1 LIMIT 1")->fetch();
        $taAktifId = $taAktif ? (int)$taAktif['id'] : 0;
        $filterTa = isset($_GET['ta']) ? (int)$_GET['ta'] : $taAktifId;

        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = max(10, min(100, intval($_GET['limit'] ?? 25)));
        $offset = ($page - 1) * $limit;
        
        $search = trim($_GET['search'] ?? '');
        $filterJenjang = trim($_GET['jenjang'] ?? '');
        $filterKelas = trim($_GET['kelas'] ?? '');
        $filterDapodik = trim($_GET['dapodik'] ?? '');
        $filterStatus = isset($_GET['status']) && $_GET['status'] !== '' ? intval($_GET['status']) : '';

        $where = 'tahun_akademik_id = ?';
        $params = [$filterTa];

        if ($search !== '') {
            $where .= " AND (nama_lengkap LIKE ? OR nama LIKE ? OR nis LIKE ? OR nisn LIKE ? OR no_nik LIKE ? OR nama_ayah LIKE ? OR nama_ibu LIKE ? OR alamat LIKE ?)";
            $s = "%{$search}%";
            $params = array_merge($params, [$s, $s, $s, $s, $s, $s, $s, $s]);
        }

        if ($filterJenjang !== '') {
            $where .= " AND UPPER(jenjang) = ?";
            $params[] = strtoupper($filterJenjang);
        }

        if ($filterKelas !== '') {
            $where .= " AND kelas = ?";
            $params[] = $filterKelas;
        }

        if ($filterDapodik !== '') {
            $where .= " AND dapodik = ?";
            $params[] = $filterDapodik;
        }

        if ($filterStatus !== '') {
            $where .= " AND is_active = ?";
            $params[] = $filterStatus;
        }

        $total = $db->find("SELECT COUNT(*) as total FROM siswa WHERE {$where}", $params)['total'] ?? 0;
        $siswa = $db->findAll("SELECT * FROM siswa WHERE {$where} ORDER BY jenjang ASC, kelas ASC, nama_lengkap ASC LIMIT {$limit} OFFSET {$offset}", $params);
        $totalPages = max(1, ceil($total / $limit));

        // Aggregate Statistics in Single Query
        $stats = $db->find("
            SELECT 
                COUNT(1) as total_siswa,
                SUM(CASE WHEN UPPER(jenjang) = 'PAUD' OR UPPER(jenjang) = 'TK' THEN 1 ELSE 0 END) as total_paud,
                SUM(CASE WHEN UPPER(jenjang) = 'SD' THEN 1 ELSE 0 END) as total_sd,
                SUM(CASE WHEN UPPER(jenjang) = 'SMP' THEN 1 ELSE 0 END) as total_smp,
                SUM(CASE WHEN UPPER(jenjang) = 'SMA' THEN 1 ELSE 0 END) as total_sma,
                SUM(CASE WHEN jenis_kelamin = 'L' OR jenis_kelamin = 'Laki-Laki' THEN 1 ELSE 0 END) as total_laki,
                SUM(CASE WHEN jenis_kelamin = 'P' OR jenis_kelamin = 'Perempuan' THEN 1 ELSE 0 END) as total_perempuan,
                SUM(CASE WHEN dapodik = 'Sudah' THEN 1 ELSE 0 END) as total_dapodik,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as total_aktif
            FROM siswa
            WHERE tahun_akademik_id = ?
        ", [$filterTa]);

        $totalSiswa = intval($stats['total_siswa'] ?? 0);
        $totalPaud = intval($stats['total_paud'] ?? 0);
        $totalSd = intval($stats['total_sd'] ?? 0);
        $totalSmp = intval($stats['total_smp'] ?? 0);
        $totalSma = intval($stats['total_sma'] ?? 0);
        $totalLaki = intval($stats['total_laki'] ?? 0);
        $totalPerempuan = intval($stats['total_perempuan'] ?? 0);
        $totalDapodik = intval($stats['total_dapodik'] ?? 0);
        $totalAktif = intval($stats['total_aktif'] ?? 0);

        // Get distinct kelas list for filter dropdown
        $kelasList = $db->findAll("SELECT DISTINCT kelas FROM siswa WHERE kelas IS NOT NULL AND kelas != '' AND tahun_akademik_id = ? ORDER BY kelas ASC", [$filterTa]);

        // Get public IP for Dapodik Web Service guidance
        $publicIp = $_SESSION['dapodik_client_ip'] ?? null;
        if (!$publicIp) {
            $ctx = stream_context_create(['http' => ['timeout' => 1]]);
            $fetchedIp = @file_get_contents('https://api.ipify.org', false, $ctx);
            if ($fetchedIp && filter_var(trim($fetchedIp), FILTER_VALIDATE_IP)) {
                $publicIp = trim($fetchedIp);
                $_SESSION['dapodik_client_ip'] = $publicIp;
            } else {
                $publicIp = '36.74.113.159';
            }
        }

        ob_start();
        include MODULES_PATH . '/kelola-siswa/views/index.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-siswa/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function foto(): void
    {
        $pageTitle = 'Galeri Foto Siswa';
        $breadcrumbs = [
            ['label' => 'Kelola Siswa', 'url' => url('kelola-siswa')],
            ['label' => 'Galeri Foto']
        ];

        $db = Database::getInstance();
        $limit = 24; // Show 24 per page for a nice grid
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $limit;

        $filterJenjang = $_GET['jenjang'] ?? '';
        $filterKelas = $_GET['kelas'] ?? '';
        $searchQuery = $_GET['search'] ?? '';
        $filterTa = $_SESSION['tahun_akademik_id'] ?? 1;

        $where = "is_active = 1 AND tahun_akademik_id = ?";
        $params = [$filterTa];

        if ($filterJenjang !== '') {
            $where .= " AND UPPER(jenjang) = ?";
            $params[] = strtoupper($filterJenjang);
        }

        if ($filterKelas !== '') {
            $where .= " AND kelas = ?";
            $params[] = $filterKelas;
        }

        if ($searchQuery !== '') {
            $where .= " AND (nama_lengkap LIKE ? OR nisn LIKE ?)";
            $params[] = "%{$searchQuery}%";
            $params[] = "%{$searchQuery}%";
        }

        $total = $db->find("SELECT COUNT(*) as total FROM siswa WHERE {$where}", $params)['total'] ?? 0;
        $siswa = $db->findAll("SELECT id, nisn, id_siswa, nama_lengkap as nama, kelas, jenjang FROM siswa WHERE {$where} ORDER BY jenjang ASC, kelas ASC, nama ASC LIMIT {$limit} OFFSET {$offset}", $params);
        $totalPages = max(1, ceil($total / $limit));

        $kelasList = $db->findAll("SELECT DISTINCT kelas FROM siswa WHERE kelas IS NOT NULL AND kelas != '' AND tahun_akademik_id = ? ORDER BY kelas ASC", [$filterTa]);

        ob_start();
        include MODULES_PATH . '/kelola-siswa/views/foto/index.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-siswa/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function create(): void
    {
        $pageTitle = 'Tambah Data Siswa';
        $breadcrumbs = [
            ['label' => 'Kelola Siswa', 'url' => url('kelola-siswa')],
            ['label' => 'Tambah Siswa']
        ];
        
        $db = Database::getInstance();
        $kelasList = $db->findAll("SELECT DISTINCT kelas FROM siswa WHERE kelas IS NOT NULL AND kelas != '' ORDER BY kelas ASC");

        ob_start();
        include MODULES_PATH . '/kelola-siswa/views/create.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-siswa/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function store(): void
    {
        if (!CSRF::validate()) {
            Response::withError(url('kelola-siswa/create'), 'Token keamanan tidak valid.');
            return;
        }

        $namaLengkap = trim($_POST['nama_lengkap'] ?? $_POST['nama'] ?? '');
        if (empty($namaLengkap)) {
            Response::withError(url('kelola-siswa/create'), 'Nama lengkap siswa wajib diisi.');
            return;
        }

        $db = Database::getInstance();

        // Calculate age if birth date is present
        $tglLahir = !empty($_POST['tgl_lahir']) ? $_POST['tgl_lahir'] : null;
        $umur = 0;
        if (!empty($tglLahir)) {
            $birth = new DateTime($tglLahir);
            $today = new DateTime('today');
            $umur = $birth->diff($today)->y;
        }

        $idSiswa = trim($_POST['id_siswa'] ?? '');
        if (empty($idSiswa)) {
            $idSiswa = 'SISWA-' . date('Y') . sprintf('%04d', rand(1, 9999));
        }

        $jk = ($_POST['jenis_kelamin'] === 'Perempuan' || $_POST['jenis_kelamin'] === 'P') ? 'P' : 'L';
        $jenjang = strtoupper(trim($_POST['jenjang'] ?? 'SD'));

        $sql = "
            INSERT INTO siswa (
                id_siswa, nis, nisn, nama_lengkap, nama, tempat_lahir, tgl_lahir, tanggal_lahir, umur,
                jenis_kelamin, no_nik, no_kk, no_registrasi_akta, kebutuhan_khusus, anak_ke, alergi, nama_alergi,
                tinggi_badan, berat_badan, asal_sekolah, alamat_sekolah, jenjang, kelas, tahun_ajaran, semester, dapodik,
                alamat, rt, rw, dusun, kelurahan, kecamatan, kota, provinsi, kode_pos, lintang, bujur,
                tempat_tinggal, moda_transportasi, no_hp, telepon, email,
                nama_ayah, nik_ayah, tahun_lahir_ayah, pendidikan_ayah, pekerjaan_ayah, kantor_ayah, jabatan_ayah, penghasilan_ayah, kebutuhan_khusus_ayah,
                nama_ibu, nik_ibu, tahun_lahir_ibu, pendidikan_ibu, pekerjaan_ibu, kantor_ibu, jabatan_ibu, penghasilan_ibu, kebutuhan_khusus_ibu,
                is_active
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?, ?,
                ?
            )
        ";

        $params = [
            $idSiswa,
            trim($_POST['nis'] ?? ''),
            trim($_POST['nisn'] ?? ''),
            $namaLengkap,
            $namaLengkap,
            trim($_POST['tempat_lahir'] ?? ''),
            $tglLahir,
            $tglLahir,
            $umur,
            $jk,
            trim($_POST['no_nik'] ?? ''),
            trim($_POST['no_kk'] ?? ''),
            trim($_POST['no_registrasi_akta'] ?? ''),
            trim($_POST['kebutuhan_khusus'] ?? ''),
            intval($_POST['anak_ke'] ?? 1),
            trim($_POST['alergi'] ?? ''),
            trim($_POST['nama_alergi'] ?? ''),
            trim($_POST['tinggi_badan'] ?? ''),
            trim($_POST['berat_badan'] ?? ''),
            trim($_POST['asal_sekolah'] ?? ''),
            trim($_POST['alamat_sekolah'] ?? ''),
            $jenjang,
            trim($_POST['kelas'] ?? ''),
            trim($_POST['tahun_ajaran'] ?? '2026/2027'),
            trim($_POST['semester'] ?? 'Ganjil'),
            trim($_POST['dapodik'] ?? 'Belum'),
            trim($_POST['alamat'] ?? ''),
            trim($_POST['rt'] ?? ''),
            trim($_POST['rw'] ?? ''),
            trim($_POST['dusun'] ?? ''),
            trim($_POST['kelurahan'] ?? ''),
            trim($_POST['kecamatan'] ?? ''),
            trim($_POST['kota'] ?? 'Palu'),
            trim($_POST['provinsi'] ?? 'Sulawesi Tengah'),
            trim($_POST['kode_pos'] ?? ''),
            trim($_POST['lintang'] ?? ''),
            trim($_POST['bujur'] ?? ''),
            trim($_POST['tempat_tinggal'] ?? 'Bersama Orang Tua'),
            trim($_POST['moda_transportasi'] ?? 'Sepeda Motor'),
            trim($_POST['no_hp'] ?? ''),
            trim($_POST['no_hp'] ?? ''),
            trim($_POST['email'] ?? ''),
            trim($_POST['nama_ayah'] ?? ''),
            trim($_POST['nik_ayah'] ?? ''),
            trim($_POST['tahun_lahir_ayah'] ?? ''),
            trim($_POST['pendidikan_ayah'] ?? ''),
            trim($_POST['pekerjaan_ayah'] ?? ''),
            trim($_POST['kantor_ayah'] ?? ''),
            trim($_POST['jabatan_ayah'] ?? ''),
            trim($_POST['penghasilan_ayah'] ?? ''),
            trim($_POST['kebutuhan_khusus_ayah'] ?? ''),
            trim($_POST['nama_ibu'] ?? ''),
            trim($_POST['nik_ibu'] ?? ''),
            trim($_POST['tahun_lahir_ibu'] ?? ''),
            trim($_POST['pendidikan_ibu'] ?? ''),
            trim($_POST['pekerjaan_ibu'] ?? ''),
            trim($_POST['kantor_ibu'] ?? ''),
            trim($_POST['jabatan_ibu'] ?? ''),
            trim($_POST['penghasilan_ibu'] ?? ''),
            trim($_POST['kebutuhan_khusus_ibu'] ?? ''),
            isset($_POST['is_active']) ? intval($_POST['is_active']) : 1
        ];

        try {
            $db->getConnection()->prepare($sql)->execute($params);
            Response::withSuccess(url('kelola-siswa'), "Data siswa '{$namaLengkap}' berhasil disimpan.");
        } catch (Exception $e) {
            Response::withError(url('kelola-siswa/create'), 'Gagal menyimpan data siswa: ' . $e->getMessage());
        }
    }

    public static function detail(int $id): void
    {
        $db = Database::getInstance();
        $siswa = $db->find("SELECT * FROM siswa WHERE id = ?", [$id]);
        if (!$siswa) {
            Response::withError(url('kelola-siswa/foto'), 'Data siswa tidak ditemukan.');
            return;
        }

        $pageTitle = 'Profil Siswa: ' . ($siswa['nama_lengkap'] ?: $siswa['nama']);
        $breadcrumbs = [
            ['label' => 'Kelola Siswa', 'url' => url('kelola-siswa')],
            ['label' => 'Profil Siswa']
        ];

        ob_start();
        include MODULES_PATH . '/kelola-siswa/views/detail.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-siswa/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function edit(int $id): void
    {
        $db = Database::getInstance();
        $siswa = $db->find("SELECT * FROM siswa WHERE id = ?", [$id]);
        if (!$siswa) {
            Response::withError(url('kelola-siswa/foto'), 'Data siswa tidak ditemukan.');
            return;
        }

        $pageTitle = 'Edit Data Siswa: ' . ($siswa['nama_lengkap'] ?: $siswa['nama']);
        $breadcrumbs = [
            ['label' => 'Kelola Siswa', 'url' => url('kelola-siswa')],
            ['label' => 'Edit Siswa']
        ];
        
        $kelasList = $db->findAll("SELECT DISTINCT kelas FROM siswa WHERE kelas IS NOT NULL AND kelas != '' ORDER BY kelas ASC");

        ob_start();
        include MODULES_PATH . '/kelola-siswa/views/edit.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-siswa/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function update(int $id): void
    {
        if (!CSRF::validate()) {
            Response::withError(url("kelola-siswa/edit/{$id}"), 'Token keamanan tidak valid.');
            return;
        }

        $namaLengkap = trim($_POST['nama_lengkap'] ?? $_POST['nama'] ?? '');
        if (empty($namaLengkap)) {
            Response::withError(url("kelola-siswa/edit/{$id}"), 'Nama lengkap siswa wajib diisi.');
            return;
        }

        $db = Database::getInstance();
        $tglLahir = !empty($_POST['tgl_lahir']) ? $_POST['tgl_lahir'] : null;
        $umur = 0;
        if (!empty($tglLahir)) {
            $birth = new DateTime($tglLahir);
            $today = new DateTime('today');
            $umur = $birth->diff($today)->y;
        }

        $jk = ($_POST['jenis_kelamin'] === 'Perempuan' || $_POST['jenis_kelamin'] === 'P') ? 'P' : 'L';
        $jenjang = strtoupper(trim($_POST['jenjang'] ?? 'SD'));

        $sql = "
            UPDATE siswa SET
                id_siswa = ?, nis = ?, nisn = ?, nama_lengkap = ?, nama = ?, tempat_lahir = ?, tgl_lahir = ?, tanggal_lahir = ?, umur = ?,
                jenis_kelamin = ?, no_nik = ?, no_kk = ?, no_registrasi_akta = ?, kebutuhan_khusus = ?, anak_ke = ?, alergi = ?, nama_alergi = ?,
                tinggi_badan = ?, berat_badan = ?, asal_sekolah = ?, alamat_sekolah = ?, jenjang = ?, kelas = ?, tahun_ajaran = ?, semester = ?, dapodik = ?,
                alamat = ?, rt = ?, rw = ?, dusun = ?, kelurahan = ?, kecamatan = ?, kota = ?, provinsi = ?, kode_pos = ?, lintang = ?, bujur = ?,
                tempat_tinggal = ?, moda_transportasi = ?, no_hp = ?, telepon = ?, email = ?,
                nama_ayah = ?, nik_ayah = ?, tahun_lahir_ayah = ?, pendidikan_ayah = ?, pekerjaan_ayah = ?, kantor_ayah = ?, jabatan_ayah = ?, penghasilan_ayah = ?, kebutuhan_khusus_ayah = ?,
                nama_ibu = ?, nik_ibu = ?, tahun_lahir_ibu = ?, pendidikan_ibu = ?, pekerjaan_ibu = ?, kantor_ibu = ?, jabatan_ibu = ?, penghasilan_ibu = ?, kebutuhan_khusus_ibu = ?,
                is_active = ?
            WHERE id = ?
        ";

        $params = [
            trim($_POST['id_siswa'] ?? ''),
            trim($_POST['nis'] ?? ''),
            trim($_POST['nisn'] ?? ''),
            $namaLengkap,
            $namaLengkap,
            trim($_POST['tempat_lahir'] ?? ''),
            $tglLahir,
            $tglLahir,
            $umur,
            $jk,
            trim($_POST['no_nik'] ?? ''),
            trim($_POST['no_kk'] ?? ''),
            trim($_POST['no_registrasi_akta'] ?? ''),
            trim($_POST['kebutuhan_khusus'] ?? ''),
            intval($_POST['anak_ke'] ?? 1),
            trim($_POST['alergi'] ?? ''),
            trim($_POST['nama_alergi'] ?? ''),
            trim($_POST['tinggi_badan'] ?? ''),
            trim($_POST['berat_badan'] ?? ''),
            trim($_POST['asal_sekolah'] ?? ''),
            trim($_POST['alamat_sekolah'] ?? ''),
            $jenjang,
            trim($_POST['kelas'] ?? ''),
            trim($_POST['tahun_ajaran'] ?? '2026/2027'),
            trim($_POST['semester'] ?? 'Ganjil'),
            trim($_POST['dapodik'] ?? 'Belum'),
            trim($_POST['alamat'] ?? ''),
            trim($_POST['rt'] ?? ''),
            trim($_POST['rw'] ?? ''),
            trim($_POST['dusun'] ?? ''),
            trim($_POST['kelurahan'] ?? ''),
            trim($_POST['kecamatan'] ?? ''),
            trim($_POST['kota'] ?? 'Palu'),
            trim($_POST['provinsi'] ?? 'Sulawesi Tengah'),
            trim($_POST['kode_pos'] ?? ''),
            trim($_POST['lintang'] ?? ''),
            trim($_POST['bujur'] ?? ''),
            trim($_POST['tempat_tinggal'] ?? 'Bersama Orang Tua'),
            trim($_POST['moda_transportasi'] ?? 'Sepeda Motor'),
            trim($_POST['no_hp'] ?? ''),
            trim($_POST['no_hp'] ?? ''),
            trim($_POST['email'] ?? ''),
            trim($_POST['nama_ayah'] ?? ''),
            trim($_POST['nik_ayah'] ?? ''),
            trim($_POST['tahun_lahir_ayah'] ?? ''),
            trim($_POST['pendidikan_ayah'] ?? ''),
            trim($_POST['pekerjaan_ayah'] ?? ''),
            trim($_POST['kantor_ayah'] ?? ''),
            trim($_POST['jabatan_ayah'] ?? ''),
            trim($_POST['penghasilan_ayah'] ?? ''),
            trim($_POST['kebutuhan_khusus_ayah'] ?? ''),
            trim($_POST['nama_ibu'] ?? ''),
            trim($_POST['nik_ibu'] ?? ''),
            trim($_POST['tahun_lahir_ibu'] ?? ''),
            trim($_POST['pendidikan_ibu'] ?? ''),
            trim($_POST['pekerjaan_ibu'] ?? ''),
            trim($_POST['kantor_ibu'] ?? ''),
            trim($_POST['jabatan_ibu'] ?? ''),
            trim($_POST['penghasilan_ibu'] ?? ''),
            trim($_POST['kebutuhan_khusus_ibu'] ?? ''),
            isset($_POST['is_active']) ? intval($_POST['is_active']) : 1,
            $id
        ];

        try {
            $db->getConnection()->prepare($sql)->execute($params);
            Response::withSuccess(url("kelola-siswa/detail/{$id}"), "Data siswa '{$namaLengkap}' berhasil diperbarui.");
        } catch (Exception $e) {
            Response::withError(url("kelola-siswa/edit/{$id}"), 'Gagal memperbarui data siswa: ' . $e->getMessage());
        }
    }

    public static function delete(int $id): void
    {
        if (!CSRF::validate()) {
            Response::withError(url('kelola-siswa/foto'), 'Token keamanan tidak valid.');
            return;
        }

        $db = Database::getInstance();
        $siswa = $db->find("SELECT nama_lengkap, nama FROM siswa WHERE id = ?", [$id]);
        if (!$siswa) {
            Response::withError(url('kelola-siswa/foto'), 'Data siswa tidak ditemukan.');
            return;
        }

        $nama = $siswa['nama_lengkap'] ?: $siswa['nama'];
        $db->getConnection()->prepare("DELETE FROM siswa WHERE id = ?")->execute([$id]);

        Response::withSuccess(url('kelola-siswa'), "Data siswa '{$nama}' berhasil dihapus.");
    }

    public static function cetak(int $id): void
    {
        $db = Database::getInstance();
        $siswa = $db->find("SELECT * FROM siswa WHERE id = ?", [$id]);
        if (!$siswa) {
            Response::withError(url('kelola-siswa/foto'), 'Data siswa tidak ditemukan.');
            return;
        }

        include MODULES_PATH . '/kelola-siswa/views/cetak.php';
        exit;
    }

    public static function syncJurnal(): void
    {
        if (!CSRF::validate()) {
            Response::withError(url('kelola-siswa/foto'), 'Token keamanan tidak valid.');
            return;
        }

        try {
            $db = Database::getInstance();
            $pdo = $db->getConnection();

            $pdoJurnal = new PDO('mysql:host=localhost;dbname=jurnal;charset=utf8mb4', 'root', '');
            $jurnalRows = $pdoJurnal->query("SELECT * FROM tb_data_siswa")->fetchAll(PDO::FETCH_ASSOC);

            $insertStmt = $pdo->prepare("
                INSERT INTO `siswa` (
                    id_siswa, tahun_akademik_id, nis, nisn, nama_lengkap, nama, tempat_lahir, tgl_lahir, tanggal_lahir, umur,
                    jenis_kelamin, no_nik, no_kk, no_registrasi_akta, kebutuhan_khusus, anak_ke, alergi, nama_alergi,
                    tinggi_badan, berat_badan, asal_sekolah, alamat_sekolah, jenjang, kelas, tahun_ajaran, semester, dapodik,
                    alamat, rt, rw, dusun, kelurahan, kecamatan, kota, provinsi, kode_pos, lintang, bujur,
                    tempat_tinggal, moda_transportasi, no_hp, telepon, email,
                    nama_ayah, nik_ayah, tahun_lahir_ayah, pendidikan_ayah, pekerjaan_ayah, kantor_ayah, jabatan_ayah, penghasilan_ayah, kebutuhan_khusus_ayah,
                    nama_ibu, nik_ibu, tahun_lahir_ibu, pendidikan_ibu, pekerjaan_ibu, kantor_ibu, jabatan_ibu, penghasilan_ibu, kebutuhan_khusus_ibu,
                    is_active
                ) VALUES (
                    :id_siswa, :tahun_akademik_id, :nis, :nisn, :nama_lengkap, :nama, :tempat_lahir, :tgl_lahir, :tanggal_lahir, :umur,
                    :jenis_kelamin, :no_nik, :no_kk, :no_registrasi_akta, :kebutuhan_khusus, :anak_ke, :alergi, :nama_alergi,
                    :tinggi_badan, :berat_badan, :asal_sekolah, :alamat_sekolah, :jenjang, :kelas, :tahun_ajaran, :semester, :dapodik,
                    :alamat, :rt, :rw, :dusun, :kelurahan, :kecamatan, :kota, :provinsi, :kode_pos, :lintang, :bujur,
                    :tempat_tinggal, :moda_transportasi, :no_hp, :telepon, :email,
                    :nama_ayah, :nik_ayah, :tahun_lahir_ayah, :pendidikan_ayah, :pekerjaan_ayah, :kantor_ayah, :jabatan_ayah, :penghasilan_ayah, :kebutuhan_khusus_ayah,
                    :nama_ibu, :nik_ibu, :tahun_lahir_ibu, :pendidikan_ibu, :pekerjaan_ibu, :kantor_ibu, :jabatan_ibu, :penghasilan_ibu, :kebutuhan_khusus_ibu,
                    1
                ) ON DUPLICATE KEY UPDATE 
                    nama_lengkap = VALUES(nama_lengkap),
                    nama = VALUES(nama),
                    jenjang = VALUES(jenjang),
                    kelas = VALUES(kelas),
                    dapodik = VALUES(dapodik),
                    no_hp = VALUES(no_hp)
            ");

            $taId = 1; // Placeholder
            $count = 0;
            foreach ($jurnalRows as $r) {
                $jk = ($r['jenis_kelamin'] === 'Perempuan' || $r['jenis_kelamin'] === 'P') ? 'P' : 'L';
                $tgl = !empty($r['tgl_lahir']) ? $r['tgl_lahir'] : null;

                $insertStmt->execute([
                    'id_siswa' => $r['id_siswa'] ?? null,
                    'tahun_akademik_id' => $taId,
                    'nis' => $r['nis'] ?? '',
                    'nisn' => $r['nisn'] ?? '',
                    'nama_lengkap' => $r['nama_lengkap'] ?? '',
                    'nama' => $r['nama_lengkap'] ?? '',
                    'tempat_lahir' => $r['tempat_lahir'] ?? null,
                    'tgl_lahir' => $tgl,
                    'tanggal_lahir' => $tgl,
                    'umur' => (int)($r['umur'] ?? 0),
                    'jenis_kelamin' => $jk,
                    'no_nik' => $r['no_nik'] ?? null,
                    'no_kk' => $r['no_kk'] ?? null,
                    'no_registrasi_akta' => $r['no_registrasi_akta'] ?? null,
                    'kebutuhan_khusus' => $r['kebutuhan_khusus'] ?? null,
                    'anak_ke' => (int)($r['anak_ke'] ?? 1),
                    'alergi' => $r['alergi'] ?? null,
                    'nama_alergi' => $r['nama_alergi'] ?? null,
                    'tinggi_badan' => $r['tinggi_badan'] ?? null,
                    'berat_badan' => $r['berat_badan'] ?? null,
                    'asal_sekolah' => $r['asal_sekolah'] ?? null,
                    'alamat_sekolah' => $r['alamat_sekolah'] ?? null,
                    'jenjang' => !empty($r['jenjang']) ? strtoupper($r['jenjang']) : 'SD',
                    'kelas' => $r['kelas'] ?? '',
                    'tahun_ajaran' => $r['tahun_ajaran'] ?? '2025/2026',
                    'semester' => $r['semester'] ?? 'Ganjil',
                    'dapodik' => $r['dapodik'] ?? 'Belum',
                    'alamat' => $r['alamat'] ?? null,
                    'rt' => $r['rt'] ?? null,
                    'rw' => $r['rw'] ?? null,
                    'dusun' => $r['dusun'] ?? null,
                    'kelurahan' => $r['kelurahan'] ?? null,
                    'kecamatan' => $r['kecamatan'] ?? null,
                    'kota' => $r['kota'] ?? null,
                    'provinsi' => $r['provinsi'] ?? null,
                    'kode_pos' => $r['kode_pos'] ?? null,
                    'lintang' => $r['lintang'] ?? null,
                    'bujur' => $r['bujur'] ?? null,
                    'tempat_tinggal' => $r['tempat_tinggal'] ?? null,
                    'moda_transportasi' => $r['moda_transportasi'] ?? null,
                    'no_hp' => $r['no_hp'] ?? null,
                    'telepon' => $r['no_hp'] ?? null,
                    'email' => $r['email'] ?? null,
                    'nama_ayah' => $r['nama_ayah'] ?? null,
                    'nik_ayah' => $r['nik_ayah'] ?? null,
                    'tahun_lahir_ayah' => $r['tahun_lahir_ayah'] ?? null,
                    'pendidikan_ayah' => $r['pendidikan_ayah'] ?? null,
                    'pekerjaan_ayah' => $r['pekerjaan_ayah'] ?? null,
                    'kantor_ayah' => $r['kantor_ayah'] ?? null,
                    'jabatan_ayah' => $r['jabatan_ayah'] ?? null,
                    'penghasilan_ayah' => $r['penghasilan_ayah'] ?? null,
                    'kebutuhan_khusus_ayah' => $r['kebutuhan_khusus_ayah'] ?? null,
                    'nama_ibu' => $r['nama_ibu'] ?? null,
                    'nik_ibu' => $r['nik_ibu'] ?? null,
                    'tahun_lahir_ibu' => $r['tahun_lahir_ibu'] ?? null,
                    'pendidikan_ibu' => $r['pendidikan_ibu'] ?? null,
                    'pekerjaan_ibu' => $r['pekerjaan_ibu'] ?? null,
                    'kantor_ibu' => $r['kantor_ibu'] ?? null,
                    'jabatan_ibu' => $r['jabatan_ibu'] ?? null,
                    'penghasilan_ibu' => $r['penghasilan_ibu'] ?? null,
                    'kebutuhan_khusus_ibu' => $r['kebutuhan_khusus_ibu'] ?? null,
                ]);
                $count++;
            }

            Response::withSuccess(url('kelola-siswa'), "Berhasil menyinkronkan {$count} data siswa dari database Dapodik/Jurnal.");
        } catch (Exception $e) {
            Response::withError(url('kelola-siswa'), 'Gagal sinkronisasi data: ' . $e->getMessage());
        }
    }

    public static function export(): void
    {
        $db = Database::getInstance();
        $search = trim($_GET['search'] ?? '');
        $filterJenjang = trim($_GET['jenjang'] ?? '');

        $where = '1=1';
        $params = [];

        if ($search !== '') {
            $where .= " AND (nama_lengkap LIKE ? OR nis LIKE ? OR nisn LIKE ?)";
            $s = "%{$search}%";
            $params = [$s, $s, $s];
        }
        if ($filterJenjang !== '') {
            $where .= " AND UPPER(jenjang) = ?";
            $params[] = strtoupper($filterJenjang);
        }

        $rows = $db->findAll("SELECT * FROM siswa WHERE {$where} ORDER BY jenjang ASC, kelas ASC, nama_lengkap ASC", $params);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=data_siswa_' . date('Ymd_His') . '.csv');

        $output = fopen('php://output', 'w');
        // Add UTF-8 BOM for Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, [
            'ID Siswa', 'NIS', 'NISN', 'Nama Lengkap', 'Jenis Kelamin', 'Jenjang', 'Kelas',
            'Tempat Lahir', 'Tanggal Lahir', 'Umur', 'NIK', 'No. KK', 'No. Akta', 'Kebutuhan Khusus',
            'Anak Ke', 'Alergi', 'Nama Alergi', 'Tinggi (cm)', 'Berat (kg)', 'Asal Sekolah',
            'Tahun Ajaran', 'Semester', 'Status Dapodik', 'Alamat', 'RT', 'RW', 'Dusun', 'Kelurahan',
            'Kecamatan', 'Kota/Kab', 'Provinsi', 'Kode Pos', 'No. HP/WA', 'Email',
            'Nama Ayah', 'NIK Ayah', 'Pendidikan Ayah', 'Pekerjaan Ayah', 'Penghasilan Ayah',
            'Nama Ibu', 'NIK Ibu', 'Pendidikan Ibu', 'Pekerjaan Ibu', 'Penghasilan Ibu', 'Status Aktif'
        ]);

        foreach ($rows as $r) {
            fputcsv($output, [
                $r['id_siswa'],
                $r['nis'],
                $r['nisn'],
                $r['nama_lengkap'] ?: $r['nama'],
                ($r['jenis_kelamin'] === 'L' || $r['jenis_kelamin'] === 'Laki-Laki') ? 'Laki-Laki' : 'Perempuan',
                $r['jenjang'],
                $r['kelas'],
                $r['tempat_lahir'],
                $r['tgl_lahir'],
                $r['umur'],
                "'" . $r['no_nik'],
                "'" . $r['no_kk'],
                $r['no_registrasi_akta'],
                $r['kebutuhan_khusus'],
                $r['anak_ke'],
                $r['alergi'],
                $r['nama_alergi'],
                $r['tinggi_badan'],
                $r['berat_badan'],
                $r['asal_sekolah'],
                $r['tahun_ajaran'],
                $r['semester'],
                $r['dapodik'],
                $r['alamat'],
                $r['rt'],
                $r['rw'],
                $r['dusun'],
                $r['kelurahan'],
                $r['kecamatan'],
                $r['kota'],
                $r['provinsi'],
                $r['kode_pos'],
                $r['no_hp'],
                $r['email'],
                $r['nama_ayah'],
                "'" . $r['nik_ayah'],
                $r['pendidikan_ayah'],
                $r['pekerjaan_ayah'],
                $r['penghasilan_ayah'],
                $r['nama_ibu'],
                "'" . $r['nik_ibu'],
                $r['pendidikan_ibu'],
                $r['pekerjaan_ibu'],
                $r['penghasilan_ibu'],
                !empty($r['is_active']) ? 'Aktif' : 'Non-Aktif'
            ]);
        }
        fclose($output);
        exit;
    }

    public static function syncDapodikOnline(): void
    {
        if (!CSRF::validate()) {
            Response::withError(url('kelola-siswa'), 'Token keamanan tidak valid.');
            return;
        }

        $serverUrl = trim($_POST['dapodik_url'] ?? 'http://36.88.33.154:5774');
        $token = trim($_POST['dapodik_token'] ?? 'z4sdZbDIem7ao9u');
        $npsn = trim($_POST['dapodik_npsn'] ?? '69979223');
        $taId = intval($_POST['tahun_akademik_id'] ?? 0);
        if ($taId <= 0) {
            $db = Database::getInstance();
            $taAktif = $db->query("SELECT id FROM tahun_akademik WHERE is_active = 1 LIMIT 1")->fetch();
            $taId = $taAktif ? (int)$taAktif['id'] : 0;
        }
        $jenjang = strtoupper(trim($_POST['jenjang'] ?? 'SD'));

        if (empty($token) || empty($npsn)) {
            Response::withError(url('kelola-siswa'), 'Token Web Service dan NPSN wajib diisi.');
            return;
        }

        $apiUrl = rtrim($serverUrl, '/') . "/WebService/getPesertaDidik?npsn=" . urlencode($npsn);

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$token}",
            "Accept: application/json"
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            Response::withError(url('kelola-siswa'), "Gagal terhubung ke server Dapodik: {$curlError}");
            return;
        }

        $data = json_decode($response, true);
        if (!$data || (isset($data['success']) && $data['success'] === false)) {
            $msg = $data['message'] ?? 'Respon server Dapodik tidak valid atau akses ditolak.';
            if (stripos($msg, 'IP Address') !== false) {
                $clientIp = $_SESSION['dapodik_client_ip'] ?? trim(@file_get_contents('https://api.ipify.org') ?: '36.74.113.159');
                $msg = "IP Address komputer Anda ({$clientIp}) belum cocok dengan yang didaftarkan di aplikasi Dapodik. Silakan buka aplikasi Dapodik di komputer/server sekolah &rarr; Masuk menu Pengaturan &rarr; Web Service &rarr; Ubah IP Address menjadi: {$clientIp}";
            }
            Response::withError(url('kelola-siswa'), "Dapodik Error: {$msg}");
            return;
        }

        $rows = $data['rows'] ?? $data['data'] ?? [];
        if (empty($rows)) {
            Response::withError(url('kelola-siswa'), 'Tidak ada data peserta didik yang ditemukan pada respon Dapodik.');
            return;
        }

        try {
            $db = Database::getInstance();
            $pdo = $db->getConnection();

            // Pastikan kolom-kolom tabel siswa berukuran memadai untuk data Dapodik
            try {
                $hasTingkat = $pdo->query("SHOW COLUMNS FROM `siswa` LIKE 'tingkat'")->fetch();
                if (!$hasTingkat) {
                    $pdo->exec("ALTER TABLE `siswa` ADD COLUMN `tingkat` VARCHAR(50) DEFAULT NULL AFTER `kelas`");
                }
            } catch (\Exception $e) {}

            $alterQueries = [
                "ALTER TABLE `siswa` MODIFY `kelas` VARCHAR(100) DEFAULT NULL",
                "ALTER TABLE `siswa` MODIFY `tingkat` VARCHAR(50) DEFAULT NULL",
                "ALTER TABLE `siswa` MODIFY `tahun_ajaran` VARCHAR(50) DEFAULT NULL",
                "ALTER TABLE `siswa` MODIFY `semester` VARCHAR(50) DEFAULT 'Ganjil'",
                "ALTER TABLE `siswa` MODIFY `telepon` VARCHAR(50) DEFAULT NULL",
                "ALTER TABLE `siswa` MODIFY `no_hp` VARCHAR(50) DEFAULT NULL",
                "ALTER TABLE `siswa` MODIFY `no_registrasi_akta` VARCHAR(100) DEFAULT NULL",
                "ALTER TABLE `siswa` MODIFY `kebutuhan_khusus` VARCHAR(100) DEFAULT NULL",
                "ALTER TABLE `siswa` MODIFY `tahun_lahir_ayah` VARCHAR(10) DEFAULT NULL",
                "ALTER TABLE `siswa` MODIFY `tahun_lahir_ibu` VARCHAR(10) DEFAULT NULL",
                "ALTER TABLE `siswa` MODIFY `asal_sekolah` VARCHAR(255) DEFAULT NULL",
                "ALTER TABLE `siswa` MODIFY `tinggi_badan` VARCHAR(20) DEFAULT NULL",
                "ALTER TABLE `siswa` MODIFY `berat_badan` VARCHAR(20) DEFAULT NULL"
            ];
            foreach ($alterQueries as $alterSql) {
                try {
                    $pdo->exec($alterSql);
                } catch (\Exception $e) {}
            }

            $safeStr = function($val, int $len = 255) {
                if ($val === null || $val === '') return null;
                return mb_substr(trim((string)$val), 0, $len);
            };

            $pdo->beginTransaction();

            // Replace existing data for the selected jenjang and academic year
            $deleteStmt = $pdo->prepare("DELETE FROM `siswa` WHERE UPPER(jenjang) = ? AND (tahun_akademik_id = ? OR tahun_akademik_id = 0)");
            $deleteStmt->execute([$jenjang, $taId]);

            $insertStmt = $pdo->prepare("
                INSERT INTO `siswa` (
                    id_siswa, tahun_akademik_id, nis, nisn, nama_lengkap, nama, tempat_lahir, tgl_lahir, tanggal_lahir, umur,
                    jenis_kelamin, no_nik, no_kk, no_registrasi_akta, kebutuhan_khusus, anak_ke, alergi, nama_alergi,
                    tinggi_badan, berat_badan, asal_sekolah, alamat_sekolah, jenjang, kelas, tingkat, tahun_ajaran, semester, dapodik,
                    alamat, rt, rw, dusun, kelurahan, kecamatan, kota, provinsi, kode_pos, lintang, bujur,
                    tempat_tinggal, moda_transportasi, no_hp, telepon, email,
                    nama_ayah, nik_ayah, tahun_lahir_ayah, pendidikan_ayah, pekerjaan_ayah, kantor_ayah, jabatan_ayah, penghasilan_ayah, kebutuhan_khusus_ayah,
                    nama_ibu, nik_ibu, tahun_lahir_ibu, pendidikan_ibu, pekerjaan_ibu, kantor_ibu, jabatan_ibu, penghasilan_ibu, kebutuhan_khusus_ibu,
                    is_active
                ) VALUES (
                    :id_siswa, :tahun_akademik_id, :nis, :nisn, :nama_lengkap, :nama, :tempat_lahir, :tgl_lahir, :tanggal_lahir, :umur,
                    :jenis_kelamin, :no_nik, :no_kk, :no_registrasi_akta, :kebutuhan_khusus, :anak_ke, :alergi, :nama_alergi,
                    :tinggi_badan, :berat_badan, :asal_sekolah, :alamat_sekolah, :jenjang, :kelas, :tingkat, :tahun_ajaran, :semester, :dapodik,
                    :alamat, :rt, :rw, :dusun, :kelurahan, :kecamatan, :kota, :provinsi, :kode_pos, :lintang, :bujur,
                    :tempat_tinggal, :moda_transportasi, :no_hp, :telepon, :email,
                    :nama_ayah, :nik_ayah, :tahun_lahir_ayah, :pendidikan_ayah, :pekerjaan_ayah, :kantor_ayah, :jabatan_ayah, :penghasilan_ayah, :kebutuhan_khusus_ayah,
                    :nama_ibu, :nik_ibu, :tahun_lahir_ibu, :pendidikan_ibu, :pekerjaan_ibu, :kantor_ibu, :jabatan_ibu, :penghasilan_ibu, :kebutuhan_khusus_ibu,
                    1
                ) ON DUPLICATE KEY UPDATE 
                    nama_lengkap = VALUES(nama_lengkap),
                    nama = VALUES(nama),
                    jenjang = VALUES(jenjang),
                    kelas = VALUES(kelas),
                    tingkat = VALUES(tingkat),
                    dapodik = 'Sudah',
                    nisn = VALUES(nisn),
                    no_nik = VALUES(no_nik),
                    no_hp = VALUES(no_hp)
            ");

            $imported = 0;
            foreach ($rows as $r) {
                $jk = ($r['jenis_kelamin'] === 'Perempuan' || $r['jenis_kelamin'] === 'P') ? 'P' : 'L';
                $tgl = !empty($r['tanggal_lahir']) ? $r['tanggal_lahir'] : null;
                $umur = 0;
                if (!empty($tgl)) {
                    try {
                        $birth = new DateTime($tgl);
                        $today = new DateTime('today');
                        $umur = $birth->diff($today)->y;
                    } catch (\Exception $e) {
                        $umur = 0;
                    }
                }

                $nama = trim($r['nama'] ?? $r['nama_lengkap'] ?? '');
                $idSiswa = $r['peserta_didik_id'] ?? ('SISWA-' . ($r['nisn'] ?: $r['nipd'] ?: rand(100000, 999999)));

                $kelasName = trim($r['nama_rombel'] ?? $r['kelas'] ?? '');
                $tingkat = '';
                if (preg_match('/(?:kelas\s*|^)([1-9]|1[0-2]|IX|IV|V?I{0,3})\b/i', $kelasName, $matches)) {
                    $tingkat = strtoupper($matches[1]);
                } elseif (preg_match('/\b([1-9]|1[0-2])\b/', $kelasName, $matches)) {
                    $tingkat = $matches[1];
                }

                // Parse tahun lahir ayah & ibu secara aman
                $thnAyah = null;
                if (!empty($r['tahun_lahir_ayah'])) {
                    if (preg_match('/\b(19\d\d|20\d\d)\b/', (string)$r['tahun_lahir_ayah'], $m)) {
                        $thnAyah = $m[1];
                    } else {
                        $thnAyah = $safeStr($r['tahun_lahir_ayah'], 10);
                    }
                }
                $thnIbu = null;
                if (!empty($r['tahun_lahir_ibu'])) {
                    if (preg_match('/\b(19\d\d|20\d\d)\b/', (string)$r['tahun_lahir_ibu'], $m)) {
                        $thnIbu = $m[1];
                    } else {
                        $thnIbu = $safeStr($r['tahun_lahir_ibu'], 10);
                    }
                }

                $insertStmt->execute([
                    'id_siswa' => $safeStr($idSiswa, 50),
                    'tahun_akademik_id' => $taId,
                    'nis' => $safeStr($r['nipd'] ?? $r['nis'] ?? '', 20) ?? '',
                    'nisn' => $safeStr($r['nisn'] ?? '', 30) ?? '',
                    'nama_lengkap' => $safeStr($nama, 150) ?? '',
                    'nama' => $safeStr($nama, 100) ?? '',
                    'tempat_lahir' => $safeStr($r['tempat_lahir'] ?? null, 100),
                    'tgl_lahir' => $tgl,
                    'tanggal_lahir' => $tgl,
                    'umur' => $umur,
                    'jenis_kelamin' => $jk,
                    'no_nik' => $safeStr($r['nik'] ?? $r['no_nik'] ?? null, 20),
                    'no_kk' => $safeStr($r['no_kk'] ?? null, 20),
                    'no_registrasi_akta' => $safeStr($r['no_registrasi_akta'] ?? null, 100),
                    'kebutuhan_khusus' => $safeStr($r['kebutuhan_khusus'] ?? null, 100),
                    'anak_ke' => (int)($r['anak_ke'] ?? 1),
                    'alergi' => $safeStr($r['alergi'] ?? null, 50),
                    'nama_alergi' => $safeStr($r['nama_alergi'] ?? null, 100),
                    'tinggi_badan' => $safeStr($r['tinggi_badan'] ?? null, 20),
                    'berat_badan' => $safeStr($r['berat_badan'] ?? null, 20),
                    'asal_sekolah' => $safeStr($r['asal_sekolah'] ?? null, 255),
                    'alamat_sekolah' => $r['alamat_sekolah'] ?? null,
                    'jenjang' => $safeStr($jenjang, 50),
                    'kelas' => $safeStr($kelasName, 100),
                    'tingkat' => $safeStr($tingkat, 50),
                    'tahun_ajaran' => $safeStr($r['tahun_ajaran'] ?? '2026/2027', 50),
                    'semester' => $safeStr($r['semester'] ?? 'Ganjil', 50),
                    'dapodik' => 'Sudah',
                    'alamat' => $r['alamat_jalan'] ?? $r['alamat'] ?? null,
                    'rt' => $safeStr($r['rt'] ?? null, 10),
                    'rw' => $safeStr($r['rw'] ?? null, 10),
                    'dusun' => $safeStr($r['nama_dusun'] ?? $r['dusun'] ?? null, 100),
                    'kelurahan' => $safeStr($r['desa_kelurahan'] ?? $r['kelurahan'] ?? null, 100),
                    'kecamatan' => $safeStr($r['kecamatan'] ?? null, 100),
                    'kota' => $safeStr($r['kabupaten_kota'] ?? $r['kota'] ?? 'Kota Palu', 100),
                    'provinsi' => $safeStr($r['provinsi'] ?? 'Sulawesi Tengah', 100),
                    'kode_pos' => $safeStr($r['kode_pos'] ?? null, 10),
                    'lintang' => $safeStr($r['lintang'] ?? null, 50),
                    'bujur' => $safeStr($r['bujur'] ?? null, 50),
                    'tempat_tinggal' => $safeStr($r['tempat_tinggal'] ?? null, 100),
                    'moda_transportasi' => $safeStr($r['moda_transportasi'] ?? null, 100),
                    'no_hp' => $safeStr($r['nomor_telepon_seluler'] ?? $r['no_hp'] ?? null, 50),
                    'telepon' => $safeStr($r['nomor_telepon_rumah'] ?? $r['telepon'] ?? null, 50),
                    'email' => $safeStr($r['email'] ?? null, 150),
                    'nama_ayah' => $safeStr($r['nama_ayah'] ?? null, 150),
                    'nik_ayah' => $safeStr($r['nik_ayah'] ?? null, 20),
                    'tahun_lahir_ayah' => $thnAyah,
                    'pendidikan_ayah' => $safeStr($r['pendidikan_ayah'] ?? null, 100),
                    'pekerjaan_ayah' => $safeStr($r['pekerjaan_ayah'] ?? null, 100),
                    'kantor_ayah' => $safeStr($r['kantor_ayah'] ?? null, 150),
                    'jabatan_ayah' => $safeStr($r['jabatan_ayah'] ?? null, 100),
                    'penghasilan_ayah' => $safeStr($r['penghasilan_ayah'] ?? null, 50),
                    'kebutuhan_khusus_ayah' => $safeStr($r['kebutuhan_khusus_ayah'] ?? null, 50),
                    'nama_ibu' => $safeStr($r['nama_ibu_kandung'] ?? $r['nama_ibu'] ?? null, 150),
                    'nik_ibu' => $safeStr($r['nik_ibu'] ?? null, 20),
                    'tahun_lahir_ibu' => $thnIbu,
                    'pendidikan_ibu' => $safeStr($r['pendidikan_ibu'] ?? null, 100),
                    'pekerjaan_ibu' => $safeStr($r['pekerjaan_ibu'] ?? null, 100),
                    'kantor_ibu' => $safeStr($r['kantor_ibu'] ?? null, 150),
                    'jabatan_ibu' => $safeStr($r['jabatan_ibu'] ?? null, 100),
                    'penghasilan_ibu' => $safeStr($r['penghasilan_ibu'] ?? null, 50),
                    'kebutuhan_khusus_ibu' => $safeStr($r['kebutuhan_khusus_ibu'] ?? null, 50),
                ]);
                $imported++;
            }

            $pdo->commit();

            Response::withSuccess(url('kelola-siswa'), "Berhasil menarik dan menyinkronkan {$imported} data peserta didik dari Dapodik Online.");
        } catch (\Exception $e) {
            if (isset($pdo) && $pdo instanceof \PDO && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            Response::withError(url('kelola-siswa'), 'Gagal menyimpan data dari Dapodik: ' . $e->getMessage());
        }
    }

    // ==========================================
    // 1. DASHBOARD & STATISTIK KESISWAAN
    // ==========================================
    public static function statistik(): void
    {
        $pageTitle = 'Dashboard & Statistik Kesiswaan';
        $breadcrumbs = [
            ['label' => 'Kelola Siswa', 'url' => url('kelola-siswa')],
            ['label' => 'Dashboard Statistik']
        ];

        $db = Database::getInstance();

        // General stats
        $stats = $db->find("
            SELECT 
                COUNT(1) as total_siswa,
                SUM(CASE WHEN UPPER(jenjang) = 'PAUD' OR UPPER(jenjang) = 'TK' THEN 1 ELSE 0 END) as total_paud,
                SUM(CASE WHEN UPPER(jenjang) = 'SD' THEN 1 ELSE 0 END) as total_sd,
                SUM(CASE WHEN UPPER(jenjang) = 'SMP' THEN 1 ELSE 0 END) as total_smp,
                SUM(CASE WHEN UPPER(jenjang) = 'SMA' THEN 1 ELSE 0 END) as total_sma,
                SUM(CASE WHEN jenis_kelamin = 'L' OR jenis_kelamin = 'Laki-Laki' THEN 1 ELSE 0 END) as total_laki,
                SUM(CASE WHEN jenis_kelamin = 'P' OR jenis_kelamin = 'Perempuan' THEN 1 ELSE 0 END) as total_perempuan,
                SUM(CASE WHEN dapodik = 'Sudah' THEN 1 ELSE 0 END) as total_dapodik,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as total_aktif
            FROM siswa
        ");

        $totalSiswa = intval($stats['total_siswa'] ?? 0);
        $totalPaud = intval($stats['total_paud'] ?? 0);
        $totalSd = intval($stats['total_sd'] ?? 0);
        $totalSmp = intval($stats['total_smp'] ?? 0);
        $totalSma = intval($stats['total_sma'] ?? 0);
        $totalLaki = intval($stats['total_laki'] ?? 0);
        $totalPerempuan = intval($stats['total_perempuan'] ?? 0);
        $totalDapodik = intval($stats['total_dapodik'] ?? 0);
        $totalAktif = intval($stats['total_aktif'] ?? 0);

        // Prestasi Stats
        $totalPrestasi = $db->find("SELECT COUNT(*) as total FROM siswa_prestasi")['total'] ?? 0;
        
        // Siswa Keluar Stats
        $totalKeluar = $db->find("SELECT COUNT(*) as total FROM siswa_keluar")['total'] ?? 0;

        // Rekap per Kelas
        $rekapKelas = $db->findAll("
            SELECT 
                kelas,
                jenjang,
                COUNT(1) as total,
                SUM(CASE WHEN jenis_kelamin = 'L' OR jenis_kelamin = 'Laki-Laki' THEN 1 ELSE 0 END) as laki,
                SUM(CASE WHEN jenis_kelamin = 'P' OR jenis_kelamin = 'Perempuan' THEN 1 ELSE 0 END) as perempuan
            FROM siswa
            WHERE is_active = 1 AND kelas IS NOT NULL AND kelas != ''
            GROUP BY kelas, jenjang
            ORDER BY jenjang ASC, kelas ASC
        ");

        ob_start();
        include MODULES_PATH . '/kelola-siswa/views/statistik.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-siswa/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    // ==========================================
    // 2. BUKU INDUK SISWA
    // ==========================================
    public static function bukuInduk(): void
    {
        $pageTitle = 'Buku Induk Siswa';
        $breadcrumbs = [
            ['label' => 'Kelola Siswa', 'url' => url('kelola-siswa')],
            ['label' => 'Buku Induk']
        ];

        $db = Database::getInstance();
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = max(10, min(100, intval($_GET['limit'] ?? 25)));
        $offset = ($page - 1) * $limit;

        $search = trim($_GET['search'] ?? '');
        $filterJenjang = trim($_GET['jenjang'] ?? '');
        $filterKelas = trim($_GET['kelas'] ?? '');

        $where = '1=1';
        $params = [];

        if ($search !== '') {
            $where .= " AND (nama_lengkap LIKE ? OR nama LIKE ? OR nis LIKE ? OR nisn LIKE ? OR no_nik LIKE ? OR nama_ayah LIKE ? OR nama_ibu LIKE ? OR alamat LIKE ?)";
            $s = "%{$search}%";
            $params = [$s, $s, $s, $s, $s, $s, $s, $s];
        }

        if ($filterJenjang !== '') {
            $where .= " AND UPPER(jenjang) = ?";
            $params[] = strtoupper($filterJenjang);
        }

        if ($filterKelas !== '') {
            $where .= " AND kelas = ?";
            $params[] = $filterKelas;
        }

        $total = $db->find("SELECT COUNT(*) as total FROM siswa WHERE {$where}", $params)['total'] ?? 0;
        $siswaList = $db->findAll("SELECT * FROM siswa WHERE {$where} ORDER BY jenjang ASC, kelas ASC, nis ASC, nama_lengkap ASC LIMIT {$limit} OFFSET {$offset}", $params);
        $totalPages = max(1, ceil($total / $limit));

        $kelasList = $db->findAll("SELECT DISTINCT kelas FROM siswa WHERE kelas IS NOT NULL AND kelas != '' ORDER BY kelas ASC");

        ob_start();
        include MODULES_PATH . '/kelola-siswa/views/buku_induk/index.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-siswa/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function detailBukuInduk(int $id): void
    {
        $db = Database::getInstance();
        $siswa = $db->find("SELECT * FROM siswa WHERE id = ?", [$id]);
        if (!$siswa) {
            Response::withError(url('kelola-siswa/buku-induk'), 'Data siswa tidak ditemukan.');
            return;
        }

        $pageTitle = 'Lembar Buku Induk: ' . ($siswa['nama_lengkap'] ?: $siswa['nama']);
        $breadcrumbs = [
            ['label' => 'Kelola Siswa', 'url' => url('kelola-siswa')],
            ['label' => 'Buku Induk', 'url' => url('kelola-siswa/buku-induk')],
            ['label' => 'Detail Lembar']
        ];

        ob_start();
        include MODULES_PATH . '/kelola-siswa/views/buku_induk/detail.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-siswa/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function cetakBukuInduk(int $id): void
    {
        $db = Database::getInstance();
        $siswa = $db->find("SELECT * FROM siswa WHERE id = ?", [$id]);
        if (!$siswa) {
            Response::withError(url('kelola-siswa/buku-induk'), 'Data siswa tidak ditemukan.');
            return;
        }

        include MODULES_PATH . '/kelola-siswa/views/buku_induk/cetak.php';
        exit;
    }

    public static function exportBukuInduk(): void
    {
        self::export();
    }

    // ==========================================
    // 3. PRESTASI & PENGHARGAAN SISWA
    // ==========================================
    public static function prestasi(): void
    {
        $pageTitle = 'Prestasi & Penghargaan Siswa';
        $breadcrumbs = [
            ['label' => 'Kelola Siswa', 'url' => url('kelola-siswa')],
            ['label' => 'Prestasi Siswa']
        ];

        $db = Database::getInstance();
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = max(10, min(100, intval($_GET['limit'] ?? 25)));
        $offset = ($page - 1) * $limit;

        $search = trim($_GET['search'] ?? '');
        $filterTingkat = trim($_GET['tingkat'] ?? '');
        $filterBidang = trim($_GET['bidang'] ?? '');

        $where = '1=1';
        $params = [];

        if ($search !== '') {
            $where .= " AND (p.nama_prestasi LIKE ? OR s.nama_lengkap LIKE ? OR s.nama LIKE ? OR p.penyelenggara LIKE ? OR p.guru_pendamping LIKE ?)";
            $s = "%{$search}%";
            $params = [$s, $s, $s, $s, $s];
        }

        if ($filterTingkat !== '') {
            $where .= " AND p.tingkat = ?";
            $params[] = $filterTingkat;
        }

        if ($filterBidang !== '') {
            $where .= " AND p.bidang = ?";
            $params[] = $filterBidang;
        }

        $total = $db->find("
            SELECT COUNT(*) as total 
            FROM siswa_prestasi p 
            JOIN siswa s ON p.siswa_id = s.id 
            WHERE {$where}
        ", $params)['total'] ?? 0;

        $prestasiList = $db->findAll("
            SELECT p.*, s.nama_lengkap, s.nama, s.nis, s.nisn, s.jenjang, s.kelas 
            FROM siswa_prestasi p 
            JOIN siswa s ON p.siswa_id = s.id 
            WHERE {$where} 
            ORDER BY p.tanggal_peroleh DESC, p.id DESC 
            LIMIT {$limit} OFFSET {$offset}
        ", $params);

        $totalPages = max(1, ceil($total / $limit));

        // Quick KPI stats
        $totalPrestasi = $db->find("SELECT COUNT(*) as total FROM siswa_prestasi")['total'] ?? 0;
        $totalNasional = $db->find("SELECT COUNT(*) as total FROM siswa_prestasi WHERE tingkat = 'Nasional' OR tingkat = 'Internasional'")['total'] ?? 0;
        $totalProvinsi = $db->find("SELECT COUNT(*) as total FROM siswa_prestasi WHERE tingkat = 'Provinsi' OR tingkat = 'Kota/Kabupaten'")['total'] ?? 0;
        $totalSiswaBerprestasi = $db->find("SELECT COUNT(DISTINCT siswa_id) as total FROM siswa_prestasi")['total'] ?? 0;

        ob_start();
        include MODULES_PATH . '/kelola-siswa/views/prestasi/index.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-siswa/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function createPrestasi(): void
    {
        $pageTitle = 'Catat Prestasi Siswa';
        $breadcrumbs = [
            ['label' => 'Kelola Siswa', 'url' => url('kelola-siswa')],
            ['label' => 'Prestasi Siswa', 'url' => url('kelola-siswa/prestasi')],
            ['label' => 'Catat Prestasi']
        ];

        $db = Database::getInstance();
        $selectedSiswaId = intval($_GET['siswa_id'] ?? 0);
        $allSiswa = $db->findAll("SELECT id, nama_lengkap, nama, nis, nisn, jenjang, kelas FROM siswa WHERE is_active = 1 ORDER BY jenjang ASC, kelas ASC, nama_lengkap ASC");

        ob_start();
        include MODULES_PATH . '/kelola-siswa/views/prestasi/create.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-siswa/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function storePrestasi(): void
    {
        if (!CSRF::validate()) {
            Response::withError(url('kelola-siswa/prestasi/create'), 'Token keamanan tidak valid.');
            return;
        }

        $siswaId = intval($_POST['siswa_id'] ?? 0);
        $namaPrestasi = trim($_POST['nama_prestasi'] ?? '');

        if ($siswaId <= 0 || empty($namaPrestasi)) {
            Response::withError(url('kelola-siswa/prestasi/create'), 'Pilih siswa dan masukkan nama prestasi.');
            return;
        }

        $fileSertifikat = null;
        if (!empty($_FILES['file_sertifikat']['name'])) {
            $uploadDir = PUBLIC_PATH . '/uploads/prestasi_siswa';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $ext = strtolower(pathinfo($_FILES['file_sertifikat']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'])) {
                $fileName = 'prestasi_' . time() . '_' . rand(100, 999) . '.' . $ext;
                if (move_uploaded_file($_FILES['file_sertifikat']['tmp_name'], $uploadDir . '/' . $fileName)) {
                    $fileSertifikat = $fileName;
                }
            }
        }

        $db = Database::getInstance();
        $sql = "
            INSERT INTO siswa_prestasi (
                siswa_id, nama_prestasi, bidang, tingkat, peringkat,
                penyelenggara, tahun, tanggal_peroleh, guru_pendamping,
                nomor_sertifikat, file_sertifikat, keterangan
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";

        $params = [
            $siswaId,
            $namaPrestasi,
            trim($_POST['bidang'] ?? 'Akademik'),
            trim($_POST['tingkat'] ?? 'Kota/Kabupaten'),
            trim($_POST['peringkat'] ?? 'Juara 1'),
            trim($_POST['penyelenggara'] ?? ''),
            trim($_POST['tahun'] ?? date('Y')),
            !empty($_POST['tanggal_peroleh']) ? $_POST['tanggal_peroleh'] : date('Y-m-d'),
            trim($_POST['guru_pendamping'] ?? ''),
            trim($_POST['nomor_sertifikat'] ?? ''),
            $fileSertifikat,
            trim($_POST['keterangan'] ?? '')
        ];

        try {
            $db->getConnection()->prepare($sql)->execute($params);
            Response::withSuccess(url('kelola-siswa/prestasi'), 'Prestasi siswa berhasil dicatat.');
        } catch (Exception $e) {
            Response::withError(url('kelola-siswa/prestasi/create'), 'Gagal menyimpan prestasi: ' . $e->getMessage());
        }
    }

    public static function editPrestasi(int $id): void
    {
        $db = Database::getInstance();
        $prestasi = $db->find("SELECT * FROM siswa_prestasi WHERE id = ?", [$id]);
        if (!$prestasi) {
            Response::withError(url('kelola-siswa/prestasi'), 'Data prestasi tidak ditemukan.');
            return;
        }

        $pageTitle = 'Edit Prestasi Siswa';
        $breadcrumbs = [
            ['label' => 'Kelola Siswa', 'url' => url('kelola-siswa')],
            ['label' => 'Prestasi Siswa', 'url' => url('kelola-siswa/prestasi')],
            ['label' => 'Edit Prestasi']
        ];

        $allSiswa = $db->findAll("SELECT id, nama_lengkap, nama, nis, nisn, jenjang, kelas FROM siswa ORDER BY jenjang ASC, kelas ASC, nama_lengkap ASC");

        ob_start();
        include MODULES_PATH . '/kelola-siswa/views/prestasi/edit.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-siswa/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function updatePrestasi(int $id): void
    {
        if (!CSRF::validate()) {
            Response::withError(url("kelola-siswa/prestasi/edit/{$id}"), 'Token keamanan tidak valid.');
            return;
        }

        $db = Database::getInstance();
        $prestasi = $db->find("SELECT * FROM siswa_prestasi WHERE id = ?", [$id]);
        if (!$prestasi) {
            Response::withError(url('kelola-siswa/prestasi'), 'Data prestasi tidak ditemukan.');
            return;
        }

        $siswaId = intval($_POST['siswa_id'] ?? 0);
        $namaPrestasi = trim($_POST['nama_prestasi'] ?? '');

        if ($siswaId <= 0 || empty($namaPrestasi)) {
            Response::withError(url("kelola-siswa/prestasi/edit/{$id}"), 'Pilih siswa dan masukkan nama prestasi.');
            return;
        }

        $fileSertifikat = $prestasi['file_sertifikat'];
        if (!empty($_FILES['file_sertifikat']['name'])) {
            $uploadDir = PUBLIC_PATH . '/uploads/prestasi_siswa';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $ext = strtolower(pathinfo($_FILES['file_sertifikat']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'])) {
                $fileName = 'prestasi_' . time() . '_' . rand(100, 999) . '.' . $ext;
                if (move_uploaded_file($_FILES['file_sertifikat']['tmp_name'], $uploadDir . '/' . $fileName)) {
                    $fileSertifikat = $fileName;
                }
            }
        }

        $sql = "
            UPDATE siswa_prestasi SET
                siswa_id = ?, nama_prestasi = ?, bidang = ?, tingkat = ?, peringkat = ?,
                penyelenggara = ?, tahun = ?, tanggal_peroleh = ?, guru_pendamping = ?,
                nomor_sertifikat = ?, file_sertifikat = ?, keterangan = ?
            WHERE id = ?
        ";

        $params = [
            $siswaId,
            $namaPrestasi,
            trim($_POST['bidang'] ?? 'Akademik'),
            trim($_POST['tingkat'] ?? 'Kota/Kabupaten'),
            trim($_POST['peringkat'] ?? 'Juara 1'),
            trim($_POST['penyelenggara'] ?? ''),
            trim($_POST['tahun'] ?? date('Y')),
            !empty($_POST['tanggal_peroleh']) ? $_POST['tanggal_peroleh'] : date('Y-m-d'),
            trim($_POST['guru_pendamping'] ?? ''),
            trim($_POST['nomor_sertifikat'] ?? ''),
            $fileSertifikat,
            trim($_POST['keterangan'] ?? ''),
            $id
        ];

        try {
            $db->getConnection()->prepare($sql)->execute($params);
            Response::withSuccess(url('kelola-siswa/prestasi'), 'Prestasi siswa berhasil diperbarui.');
        } catch (Exception $e) {
            Response::withError(url("kelola-siswa/prestasi/edit/{$id}"), 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    public static function deletePrestasi(int $id): void
    {
        if (!CSRF::validate()) {
            Response::withError(url('kelola-siswa/prestasi'), 'Token keamanan tidak valid.');
            return;
        }

        $db = Database::getInstance();
        $db->getConnection()->prepare("DELETE FROM siswa_prestasi WHERE id = ?")->execute([$id]);
        Response::withSuccess(url('kelola-siswa/prestasi'), 'Data prestasi berhasil dihapus.');
    }

    public static function timelinePrestasiSiswa(int $siswaId): void
    {
        $db = Database::getInstance();
        $siswa = $db->find("SELECT * FROM siswa WHERE id = ?", [$siswaId]);
        if (!$siswa) {
            Response::withError(url('kelola-siswa/prestasi'), 'Data siswa tidak ditemukan.');
            return;
        }

        $pageTitle = 'Prestasi Siswa: ' . ($siswa['nama_lengkap'] ?: $siswa['nama']);
        $breadcrumbs = [
            ['label' => 'Kelola Siswa', 'url' => url('kelola-siswa')],
            ['label' => 'Prestasi Siswa', 'url' => url('kelola-siswa/prestasi')],
            ['label' => 'Rekap Prestasi Perorangan']
        ];

        $prestasiList = $db->findAll("SELECT * FROM siswa_prestasi WHERE siswa_id = ? ORDER BY tanggal_peroleh DESC, id DESC", [$siswaId]);

        ob_start();
        include MODULES_PATH . '/kelola-siswa/views/prestasi/detail_siswa.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-siswa/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    // ==========================================
    // 4. SISWA KELUAR & MUTASI
    // ==========================================
    public static function siswaKeluar(): void
    {
        $pageTitle = 'Siswa Keluar & Mutasi';
        $breadcrumbs = [
            ['label' => 'Kelola Siswa', 'url' => url('kelola-siswa')],
            ['label' => 'Siswa Keluar & Mutasi']
        ];

        $db = Database::getInstance();
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = max(10, min(100, intval($_GET['limit'] ?? 25)));
        $offset = ($page - 1) * $limit;

        $search = trim($_GET['search'] ?? '');
        $filterJenis = trim($_GET['jenis'] ?? '');

        $where = '1=1';
        $params = [];

        if ($search !== '') {
            $where .= " AND (s.nama_lengkap LIKE ? OR s.nama LIKE ? OR s.nis LIKE ? OR s.nisn LIKE ? OR k.sekolah_tujuan LIKE ? OR k.alasan_keluar LIKE ?)";
            $s = "%{$search}%";
            $params = [$s, $s, $s, $s, $s, $s];
        }

        if ($filterJenis !== '') {
            $where .= " AND k.jenis_keluar = ?";
            $params[] = $filterJenis;
        }

        $total = $db->find("
            SELECT COUNT(*) as total 
            FROM siswa_keluar k 
            JOIN siswa s ON k.siswa_id = s.id 
            WHERE {$where}
        ", $params)['total'] ?? 0;

        $keluarList = $db->findAll("
            SELECT k.*, s.nama_lengkap, s.nama, s.nis, s.nisn, s.jenjang, s.kelas, s.tempat_lahir, s.tgl_lahir, s.nama_ayah, s.nama_ibu, s.alamat 
            FROM siswa_keluar k 
            JOIN siswa s ON k.siswa_id = s.id 
            WHERE {$where} 
            ORDER BY k.tanggal_keluar DESC, k.id DESC 
            LIMIT {$limit} OFFSET {$offset}
        ", $params);

        $totalPages = max(1, ceil($total / $limit));

        ob_start();
        include MODULES_PATH . '/kelola-siswa/views/keluar/index.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-siswa/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function createSiswaKeluar(): void
    {
        $pageTitle = 'Proses Siswa Keluar / Mutasi';
        $breadcrumbs = [
            ['label' => 'Kelola Siswa', 'url' => url('kelola-siswa')],
            ['label' => 'Siswa Keluar & Mutasi', 'url' => url('kelola-siswa/keluar')],
            ['label' => 'Proses Keluar']
        ];

        $db = Database::getInstance();
        $selectedSiswaId = intval($_GET['siswa_id'] ?? 0);
        $allSiswaAktif = $db->findAll("SELECT id, nama_lengkap, nama, nis, nisn, jenjang, kelas FROM siswa WHERE is_active = 1 ORDER BY jenjang ASC, kelas ASC, nama_lengkap ASC");

        ob_start();
        include MODULES_PATH . '/kelola-siswa/views/keluar/create.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-siswa/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function storeSiswaKeluar(): void
    {
        if (!CSRF::validate()) {
            Response::withError(url('kelola-siswa/keluar/create'), 'Token keamanan tidak valid.');
            return;
        }

        $siswaId = intval($_POST['siswa_id'] ?? 0);
        $jenisKeluar = trim($_POST['jenis_keluar'] ?? 'Mutasi Keluar');
        $tanggalKeluar = !empty($_POST['tanggal_keluar']) ? $_POST['tanggal_keluar'] : date('Y-m-d');

        if ($siswaId <= 0) {
            Response::withError(url('kelola-siswa/keluar/create'), 'Pilih siswa yang akan diproses keluar.');
            return;
        }

        $db = Database::getInstance();
        $siswa = $db->find("SELECT * FROM siswa WHERE id = ?", [$siswaId]);
        if (!$siswa) {
            Response::withError(url('kelola-siswa/keluar/create'), 'Data siswa tidak ditemukan.');
            return;
        }

        $fileSurat = null;
        if (!empty($_FILES['file_surat']['name'])) {
            $uploadDir = PUBLIC_PATH . '/uploads/mutasi_siswa';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $ext = strtolower(pathinfo($_FILES['file_surat']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'])) {
                $fileName = 'mutasi_' . time() . '_' . rand(100, 999) . '.' . $ext;
                if (move_uploaded_file($_FILES['file_surat']['tmp_name'], $uploadDir . '/' . $fileName)) {
                    $fileSurat = $fileName;
                }
            }
        }

        $sql = "
            INSERT INTO siswa_keluar (
                siswa_id, jenis_keluar, tanggal_keluar, tahun_ajaran,
                kelas_terakhir, alasan_keluar, sekolah_tujuan, nomor_surat,
                file_surat, catatan
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";

        $params = [
            $siswaId,
            $jenisKeluar,
            $tanggalKeluar,
            trim($_POST['tahun_ajaran'] ?? $siswa['tahun_ajaran'] ?? '2026/2027'),
            $siswa['kelas'],
            trim($_POST['alasan_keluar'] ?? ''),
            trim($_POST['sekolah_tujuan'] ?? ''),
            trim($_POST['nomor_surat'] ?? ''),
            $fileSurat,
            trim($_POST['catatan'] ?? '')
        ];

        try {
            $db->getConnection()->prepare($sql)->execute($params);
            
            // Set siswa status to non-active and status_siswa to the selected reason
            $db->getConnection()->prepare("UPDATE siswa SET is_active = 0, status_siswa = ? WHERE id = ?")->execute([$jenisKeluar, $siswaId]);

            $nama = $siswa['nama_lengkap'] ?: $siswa['nama'];
            Response::withSuccess(url('kelola-siswa/keluar'), "Status siswa '{$nama}' berhasil diubah menjadi {$jenisKeluar}.");
        } catch (Exception $e) {
            Response::withError(url('kelola-siswa/keluar/create'), 'Gagal memproses data keluar: ' . $e->getMessage());
        }
    }

    public static function editSiswaKeluar(int $id): void
    {
        $db = Database::getInstance();
        $keluar = $db->find("
            SELECT k.*, s.nama_lengkap, s.nama, s.nis, s.nisn, s.jenjang, s.kelas 
            FROM siswa_keluar k 
            JOIN siswa s ON k.siswa_id = s.id 
            WHERE k.id = ?
        ", [$id]);

        if (!$keluar) {
            Response::withError(url('kelola-siswa/keluar'), 'Data siswa keluar tidak ditemukan.');
            return;
        }

        $pageTitle = 'Edit Data Siswa Keluar / Mutasi';
        $breadcrumbs = [
            ['label' => 'Kelola Siswa', 'url' => url('kelola-siswa')],
            ['label' => 'Siswa Keluar & Mutasi', 'url' => url('kelola-siswa/keluar')],
            ['label' => 'Edit Data']
        ];

        ob_start();
        include MODULES_PATH . '/kelola-siswa/views/keluar/edit.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-siswa/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function updateSiswaKeluar(int $id): void
    {
        if (!CSRF::validate()) {
            Response::withError(url("kelola-siswa/keluar/edit/{$id}"), 'Token keamanan tidak valid.');
            return;
        }

        $db = Database::getInstance();
        $keluar = $db->find("SELECT * FROM siswa_keluar WHERE id = ?", [$id]);
        if (!$keluar) {
            Response::withError(url('kelola-siswa/keluar'), 'Data tidak ditemukan.');
            return;
        }

        $fileSurat = $keluar['file_surat'];
        if (!empty($_FILES['file_surat']['name'])) {
            $uploadDir = PUBLIC_PATH . '/uploads/mutasi_siswa';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $ext = strtolower(pathinfo($_FILES['file_surat']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'])) {
                $fileName = 'mutasi_' . time() . '_' . rand(100, 999) . '.' . $ext;
                if (move_uploaded_file($_FILES['file_surat']['tmp_name'], $uploadDir . '/' . $fileName)) {
                    $fileSurat = $fileName;
                }
            }
        }

        $jenisKeluar = trim($_POST['jenis_keluar'] ?? 'Mutasi Keluar');
        $sql = "
            UPDATE siswa_keluar SET
                jenis_keluar = ?, tanggal_keluar = ?, tahun_ajaran = ?,
                sekolah_tujuan = ?, nomor_surat = ?, file_surat = ?,
                alasan_keluar = ?, catatan = ?
            WHERE id = ?
        ";

        $params = [
            $jenisKeluar,
            !empty($_POST['tanggal_keluar']) ? $_POST['tanggal_keluar'] : date('Y-m-d'),
            trim($_POST['tahun_ajaran'] ?? '2026/2027'),
            trim($_POST['sekolah_tujuan'] ?? ''),
            trim($_POST['nomor_surat'] ?? ''),
            $fileSurat,
            trim($_POST['alasan_keluar'] ?? ''),
            trim($_POST['catatan'] ?? ''),
            $id
        ];

        try {
            $db->getConnection()->prepare($sql)->execute($params);
            $db->getConnection()->prepare("UPDATE siswa SET status_siswa = ? WHERE id = ?")->execute([$jenisKeluar, $keluar['siswa_id']]);

            Response::withSuccess(url('kelola-siswa/keluar'), 'Data siswa keluar berhasil diperbarui.');
        } catch (Exception $e) {
            Response::withError(url("kelola-siswa/keluar/edit/{$id}"), 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    public static function deleteSiswaKeluar(int $id): void
    {
        if (!CSRF::validate()) {
            Response::withError(url('kelola-siswa/keluar'), 'Token keamanan tidak valid.');
            return;
        }

        $db = Database::getInstance();
        $db->getConnection()->prepare("DELETE FROM siswa_keluar WHERE id = ?")->execute([$id]);
        Response::withSuccess(url('kelola-siswa/keluar'), 'Catatan siswa keluar berhasil dihapus.');
    }

    public static function reaktivasiSiswa(int $id): void
    {
        if (!CSRF::validate()) {
            Response::withError(url('kelola-siswa/keluar'), 'Token keamanan tidak valid.');
            return;
        }

        $db = Database::getInstance();
        $keluar = $db->find("SELECT * FROM siswa_keluar WHERE id = ?", [$id]);
        if (!$keluar) {
            Response::withError(url('kelola-siswa/keluar'), 'Data tidak ditemukan.');
            return;
        }

        $siswaId = $keluar['siswa_id'];
        $db->getConnection()->prepare("UPDATE siswa SET is_active = 1, status_siswa = 'Aktif' WHERE id = ?")->execute([$siswaId]);
        $db->getConnection()->prepare("DELETE FROM siswa_keluar WHERE id = ?")->execute([$id]);

        Response::withSuccess(url('kelola-siswa'), 'Siswa berhasil diaktifkan kembali ke data siswa aktif.');
    }

    public static function cetakSuratPindah(int $id): void
    {
        $db = Database::getInstance();
        $keluar = $db->find("
            SELECT k.*, s.nama_lengkap, s.nama, s.nis, s.nisn, s.jenjang, s.kelas, s.tempat_lahir, s.tgl_lahir, s.nama_ayah, s.nama_ibu, s.alamat, s.jenis_kelamin 
            FROM siswa_keluar k 
            JOIN siswa s ON k.siswa_id = s.id 
            WHERE k.id = ?
        ", [$id]);

        if (!$keluar) {
            Response::withError(url('kelola-siswa/keluar'), 'Data tidak ditemukan.');
            return;
        }

        include MODULES_PATH . '/kelola-siswa/views/keluar/cetak_surat.php';
        exit;
    }

    /**
     * Upload single photo for a student
     */
    public static function uploadFoto(): void
    {
        if (!CSRF::validate()) {
            Response::withError(url('kelola-siswa/foto'), 'Token keamanan tidak valid.');
            return;
        }

        $id = intval($_POST['siswa_id'] ?? 0);
        if ($id <= 0 || empty($_FILES['foto']['name'])) {
            Response::withError(url('kelola-siswa/foto'), 'Siswa atau file foto tidak ditemukan.');
            return;
        }

        $db = Database::getInstance();
        $siswa = $db->find("SELECT nisn, id_siswa FROM siswa WHERE id = ?", [$id]);
        if (!$siswa) {
            Response::withError(url('kelola-siswa/foto'), 'Data siswa tidak ditemukan.');
            return;
        }

        $identifier = !empty($siswa['nisn']) ? $siswa['nisn'] : $siswa['id_siswa'];
        $uploadDir = BASE_PATH . '/public/uploads/siswa/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $tmpPath = $_FILES['foto']['tmp_name'];
        $destPath = $uploadDir . $identifier . '.jpg';

        try {
            self::compressImage($tmpPath, $destPath, 700);
            Response::withSuccess(url('kelola-siswa/foto'), 'Foto berhasil diunggah dan dikompresi.');
        } catch (Exception $e) {
            Response::withError(url('kelola-siswa/foto'), 'Gagal mengunggah foto: ' . $e->getMessage());
        }
    }

    /**
     * Mass upload photos via ZIP
     */
    public static function uploadFotoZip(): void
    {
        if (!CSRF::validate()) {
            Response::withError(url('kelola-siswa/foto'), 'Token keamanan tidak valid.');
            return;
        }

        if (empty($_FILES['foto_zip']['name'])) {
            Response::withError(url('kelola-siswa/foto'), 'File ZIP tidak ditemukan.');
            return;
        }

        $tmpPath = $_FILES['foto_zip']['tmp_name'];
        $zip = new ZipArchive;
        
        if ($zip->open($tmpPath) === true) {
            $uploadDir = BASE_PATH . '/public/uploads/siswa/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $tempExtractDir = BASE_PATH . '/scratch/temp_zip_' . time() . '/';
            mkdir($tempExtractDir, 0777, true);
            
            $zip->extractTo($tempExtractDir);
            $zip->close();

            $successCount = 0;
            $db = Database::getInstance();

            // Recursively find images in the extracted directory
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tempExtractDir));
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $ext = strtolower($file->getExtension());
                    if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                        $filename = pathinfo($file->getFilename(), PATHINFO_FILENAME);
                        
                        // Cek apakah ada siswa dengan NISN = $filename atau id_siswa = $filename
                        $siswa = $db->find("SELECT id FROM siswa WHERE nisn = ? OR id_siswa = ?", [$filename, $filename]);
                        
                        if ($siswa) {
                            $destPath = $uploadDir . $filename . '.jpg';
                            try {
                                self::compressImage($file->getPathname(), $destPath, 700);
                                $successCount++;
                            } catch (Exception $e) {
                                // Skip failed compression
                            }
                        }
                    }
                }
            }

            // Cleanup temp directory
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($tempExtractDir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($files as $fileinfo) {
                $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
                $todo($fileinfo->getRealPath());
            }
            rmdir($tempExtractDir);

            Response::withSuccess(url('kelola-siswa/foto'), "Berhasil mengunggah dan memproses $successCount foto.");
        } else {
            Response::withError(url('kelola-siswa/foto'), 'Gagal membuka file ZIP.');
        }
    }

    /**
     * Compress image to a max size (KB) and save as JPEG
     */
    private static function compressImage($sourcePath, $destinationPath, $maxSizeKb = 700)
    {
        $info = getimagesize($sourcePath);
        if ($info === false) {
            throw new Exception("File bukan gambar yang valid.");
        }

        $mime = $info['mime'];

        switch ($mime) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $image = imagecreatefrompng($sourcePath);
                // Convert transparent background to white
                $bg = imagecreatetruecolor(imagesx($image), imagesy($image));
                imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
                imagealphablending($bg, TRUE);
                imagecopy($bg, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
                imagedestroy($image);
                $image = $bg;
                break;
            default:
                throw new Exception("Format gambar tidak didukung. Harap gunakan JPG atau PNG.");
        }

        if (!$image) {
            throw new Exception("Gagal memproses gambar.");
        }

        $quality = 90;
        // First attempt
        imagejpeg($image, $destinationPath, $quality);

        // Reduce quality if file is too large (maxSizeKb)
        while (filesize($destinationPath) > $maxSizeKb * 1024 && $quality > 10) {
            $quality -= 10;
            imagejpeg($image, $destinationPath, $quality);
        }

        imagedestroy($image);
    }

    /**
     * Cetak Kartu Siswa (Single)
     */
    public static function cetakKartu(int $id): void
    {
        $db = Database::getInstance();
        $siswa = $db->find("SELECT id, id_siswa, nisn, nama_lengkap, jenjang FROM siswa WHERE id = ?", [$id]);
        
        if (!$siswa) {
            Response::withError(url('kelola-siswa'), 'Data siswa tidak ditemukan.');
            return;
        }

        $siswaList = [$siswa];
        include MODULES_PATH . '/kelola-siswa/views/kartu.php';
        exit;
    }

    /**
     * Cetak Kartu Siswa Massal (Berdasarkan Filter)
     */
    public static function cetakKartuMassal(): void
    {
        $db = Database::getInstance();
        $filterTa = $_SESSION['tahun_akademik_id'] ?? 1;
        $filterJenjang = $_GET['jenjang'] ?? '';
        $filterKelas = $_GET['kelas'] ?? '';
        $searchQuery = $_GET['search'] ?? '';

        $where = "is_active = 1 AND tahun_akademik_id = ?";
        $params = [$filterTa];

        if ($filterJenjang !== '') {
            $where .= " AND UPPER(jenjang) = ?";
            $params[] = strtoupper($filterJenjang);
        }
        if ($filterKelas !== '') {
            $where .= " AND kelas = ?";
            $params[] = $filterKelas;
        }
        if ($searchQuery !== '') {
            $where .= " AND (nama_lengkap LIKE ? OR nisn LIKE ?)";
            $params[] = "%{$searchQuery}%";
            $params[] = "%{$searchQuery}%";
        }

        // Limit to 500 to prevent browser crash, order by same as view
        $siswaList = $db->findAll("SELECT id, id_siswa, nisn, nama_lengkap, jenjang FROM siswa WHERE {$where} ORDER BY jenjang ASC, kelas ASC, nama_lengkap ASC LIMIT 500", $params);
        
        if (empty($siswaList)) {
            Response::withError(url('kelola-siswa/foto'), 'Tidak ada data siswa yang cocok dengan filter untuk dicetak.');
            return;
        }

        include MODULES_PATH . '/kelola-siswa/views/kartu.php';
        exit;
    }

    /**
     * Upload Template Kartu (PAUD, SD, SMP, SMA)
     */
    public static function uploadTemplateKartu(): void
    {
        if (!CSRF::validate()) {
            Response::withError(url('kelola-siswa/foto'), 'Token keamanan tidak valid.');
            return;
        }

        $jenjang = strtolower($_POST['jenjang'] ?? '');
        if (!in_array($jenjang, ['paud', 'sd', 'smp', 'sma'])) {
            Response::withError(url('kelola-siswa/foto'), 'Jenjang tidak valid.');
            return;
        }

        if (empty($_FILES['template']['name'])) {
            Response::withError(url('kelola-siswa/foto'), 'File template tidak ditemukan.');
            return;
        }

        $uploadDir = BASE_PATH . '/public/uploads/templates/kartu/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $tmpPath = $_FILES['template']['tmp_name'];
        $destPath = $uploadDir . 'template_' . $jenjang . '.png';

        // We only allow PNG for templates to support transparency and good quality
        $info = getimagesize($tmpPath);
        if ($info === false || !in_array($info['mime'], ['image/png', 'image/jpeg'])) {
            Response::withError(url('kelola-siswa/foto'), 'Format file harus berupa PNG atau JPG.');
            return;
        }

        // Just move the uploaded file directly (overwriting if exists)
        if (move_uploaded_file($tmpPath, $destPath)) {
            Response::withSuccess(url('kelola-siswa/foto'), 'Template Kartu ' . strtoupper($jenjang) . ' berhasil diunggah.');
        } else {
            Response::withError(url('kelola-siswa/foto'), 'Gagal mengunggah template.');
        }
    }

    /**
     * Validasi Kartu Siswa (Public Endpoint)
     */
    public static function validasiKartu(string $identifier): void
    {
        $db = Database::getInstance();
        
        // Find by NISN, ID Siswa, or NIS
        $siswa = $db->find("
            SELECT id, id_siswa, nis, nisn, nama_lengkap, nama, jenjang, kelas, tingkat, tahun_ajaran, semester, dapodik, is_active, updated_at 
            FROM siswa 
            WHERE nisn = ? OR id_siswa = ? OR nis = ?
        ", [$identifier, $identifier, $identifier]);

        if (!$siswa) {
            http_response_code(404);
            // Show a simple error view
            echo "<!DOCTYPE html><html><head><meta name='viewport' content='width=device-width, initial-scale=1.0'><title>Tidak Valid</title><script src='https://cdn.tailwindcss.com'></script></head><body class='bg-slate-100 flex items-center justify-center min-h-screen p-4'><div class='bg-white p-8 rounded-3xl shadow-xl max-w-sm w-full text-center border-t-4 border-red-500'><div class='w-20 h-20 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-5'><svg class='w-10 h-10' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 18L18 6M6 6l12 12'/></svg></div><h2 class='text-2xl font-extrabold text-slate-800 mb-2'>Kartu Tidak Valid</h2><p class='text-slate-500 text-sm leading-relaxed mb-6'>Mohon maaf, QR Code yang Anda scan tidak dapat ditemukan di dalam sistem kami.</p><div class='text-xs font-semibold text-slate-400'>Divisi IT Bina Insan Palu</div></div></body></html>";
            return;
        }

        $jenjang = strtolower($siswa['jenjang'] ?? 'sd');
        if ($jenjang === 'tk') $jenjang = 'paud';

        $identifierFoto = !empty($siswa['nisn']) ? $siswa['nisn'] : $siswa['id_siswa'];
        $fotoPath = BASE_PATH . '/public/uploads/siswa/' . $identifierFoto . '.jpg';
        $fotoUrl = file_exists($fotoPath) ? asset('uploads/siswa/' . $identifierFoto . '.jpg') : null;

        include MODULES_PATH . '/kelola-siswa/views/validasi_kartu.php';
        exit;
    }
}
