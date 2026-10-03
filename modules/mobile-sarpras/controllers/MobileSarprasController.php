<?php
/**
 * Mobile Sarpras Controller (PWA)
 */

require_once MODULES_PATH . '/kelola-sarpras/models/SarprasModel.php';

class MobileSarprasController
{
    private static function render(string $view, array $data = []): void
    {
        // Hapus sembarang output (BOM / spasi) yang tidak sengaja ter-echo oleh file-file PHP sebelumnya
        if (ob_get_level() > 0 && ob_get_length() > 0) {
            ob_clean();
        }

        extract($data);
        $activeTab = $data['activeTab'] ?? 'beranda';
        
        ob_start();
        include MODULES_PATH . '/mobile-sarpras/views/' . $view . '.php';
        $content = ob_get_clean();
        
        include MODULES_PATH . '/mobile-sarpras/views/layout/main.php';
    }

    public static function dashboard(): void
    {
        $statistik = SarprasModel::getStatistik();
        
        $db = SarprasModel::db();
        $userId = Auth::id();
        $nama = Auth::user()['full_name'] ?? 'User';
        $jabatan = 'Administrator';
        
        try {
            $pegawai = $db->find("SELECT jabatan FROM pegawai WHERE user_id = ?", [$userId]);
            if ($pegawai && !empty($pegawai['jabatan'])) {
                $jabatan = $pegawai['jabatan'];
            } else {
                $role = $db->find("SELECT r.name FROM roles r JOIN user_roles ur ON r.id = ur.role_id WHERE ur.user_id = ? ORDER BY r.id ASC LIMIT 1", [$userId]);
                if ($role && !empty($role['name'])) {
                    $jabatan = $role['name'];
                }
            }
        } catch (Throwable $e) {
            $role = $db->find("SELECT r.name FROM roles r JOIN user_roles ur ON r.id = ur.role_id WHERE ur.user_id = ? ORDER BY r.id ASC LIMIT 1", [$userId]);
            if ($role && !empty($role['name'])) {
                $jabatan = $role['name'];
            }
        }

        // Custom mobile dashboard stats
        self::render('dashboard', [
            'pageTitle' => 'Dashboard Sarpras',
            'activeTab' => 'beranda',
            'statistik' => $statistik,
            'nama'      => $nama,
            'jabatan'   => $jabatan
        ]);
    }

    public static function scan(): void
    {
        self::render('scan', [
            'pageTitle' => 'Scan QR Code',
            'activeTab' => 'scan'
        ]);
    }

    public static function detail(int $id): void
    {
        $barang = SarprasModel::getBarangById($id);
        if (!$barang) {
            die("Barang tidak ditemukan.");
        }
        
        // Fetch maintenance history
        $db = SarprasModel::db();
        $maintenance = $db->findAll("SELECT * FROM sarpras_maintenance WHERE barang_id = ? ORDER BY tanggal_lapor DESC", [$id]);
        
        self::render('detail', [
            'pageTitle' => 'Detail Aset',
            'activeTab' => 'scan',
            'barang' => $barang,
            'maintenance' => $maintenance
        ]);
    }

    public static function inventarisList(): void
    {
        $filters = [
            'search' => trim($_GET['search'] ?? ''),
            'kategori_id' => trim($_GET['kategori_id'] ?? '')
        ];
        
        // Let's limit to 50 for mobile view to prevent heavy loading
        $barang = SarprasModel::getBarangList($filters, 50, 0);
        $kategoriList = SarprasModel::getAllKategori();
        
        self::render('inventaris/index', [
            'pageTitle' => 'Daftar Inventaris',
            'activeTab' => 'inventaris',
            'barang' => $barang,
            'filters' => $filters,
            'kategoriList' => $kategoriList
        ]);
    }

    public static function tanahList(): void
    {
        self::render('tanah/index', [
            'pageTitle' => 'Data Tanah',
            'activeTab' => 'tanah',
            'tanah' => SarprasModel::db()->query("SELECT * FROM sarpras_tanah ORDER BY created_at DESC")->fetchAll()
        ]);
    }

    public static function bangunanList(): void
    {
        self::render('bangunan/index', [
            'pageTitle' => 'Data Bangunan',
            'activeTab' => 'bangunan',
            'bangunan' => SarprasModel::db()->query("SELECT b.*, t.nama_tanah FROM sarpras_bangunan b LEFT JOIN sarpras_tanah t ON b.tanah_id = t.id ORDER BY b.created_at DESC")->fetchAll()
        ]);
    }

    public static function ruanganList(): void
    {
        self::render('ruangan/index', [
            'pageTitle' => 'Data Ruangan',
            'activeTab' => 'ruangan',
            'ruangan' => SarprasModel::db()->query("SELECT r.*, b.nama_bangunan FROM sarpras_ruangan r LEFT JOIN sarpras_bangunan b ON r.bangunan_id = b.id ORDER BY r.created_at DESC")->fetchAll()
        ]);
    }

    public static function maintenanceList(): void
    {
        self::render('maintenance/index', [
            'pageTitle' => 'Data Perbaikan',
            'activeTab' => 'maintenance',
            'maintenance' => SarprasModel::db()->query("SELECT m.*, b.nama_barang, b.kode_barang FROM sarpras_maintenance m JOIN sarpras_barang b ON m.barang_id = b.id ORDER BY m.created_at DESC")->fetchAll()
        ]);
    }

    public static function inventarisTambah(): void
    {
        self::render('inventaris/tambah', [
            'pageTitle' => 'Tambah Inventaris',
            'activeTab' => 'input',
            'golonganList' => SarprasModel::getAllGolongan(),
            'kelompokList' => SarprasModel::getAllKelompok(),
            'asalAnggaranList' => SarprasModel::getAllAsalAnggaran(),
            'satuanList' => SarprasModel::getAllSatuan()
        ]);
    }


    public static function inventarisEdit(int $id): void
    {
        $item = SarprasModel::getBarangById($id);
        if (!$item) {
            header('Location: ' . url('mobile-sarpras/inventaris'));
            exit;
        }
        self::render('inventaris/edit', [
            'pageTitle' => 'Edit Inventaris',
            'activeTab' => 'input',
            'item' => $item,
            'golonganList' => SarprasModel::getAllGolongan(),
            'kelompokList' => SarprasModel::getAllKelompok(),
            'asalAnggaranList' => SarprasModel::getAllAsalAnggaran(),
            'satuanList' => SarprasModel::getAllSatuan()
        ]);
    }

    public static function tanahTambah(): void
    {
        self::render('tanah/tambah', [
            'pageTitle' => 'Tambah Tanah',
            'activeTab' => 'input',
            'asalAnggaranList' => SarprasModel::getAllAsalAnggaran()
        ]);
    }

    public static function bangunanTambah(): void
    {
        self::render('bangunan/tambah', [
            'pageTitle' => 'Tambah Bangunan',
            'activeTab' => 'input',
            'tanahList' => SarprasModel::getTanahList(),
            'asalAnggaranList' => SarprasModel::getAllAsalAnggaran()
        ]);
    }

    public static function ruanganTambah(): void
    {
        self::render('ruangan/tambah', [
            'pageTitle' => 'Tambah Ruangan',
            'activeTab' => 'input',
            'bangunanList' => SarprasModel::getBangunanList(),
            'pegawaiList' => SarprasModel::getPegawaiList(),
            'kelasListByUnit' => SarprasModel::getKelasByUnit()
        ]);
    }

    public static function tanahEdit(int $id): void
    {
        $item = SarprasModel::getTanahById($id);
        if (!$item) {
            header('Location: ' . url('mobile-sarpras/tanah'));
            exit;
        }
        self::render('tanah/edit', [
            'pageTitle' => 'Edit Tanah',
            'activeTab' => 'input',
            'item' => $item,
            'asalAnggaranList' => SarprasModel::getAllAsalAnggaran()
        ]);
    }

    public static function bangunanEdit(int $id): void
    {
        $item = SarprasModel::getBangunanById($id);
        if (!$item) {
            header('Location: ' . url('mobile-sarpras/bangunan'));
            exit;
        }
        self::render('bangunan/edit', [
            'pageTitle' => 'Edit Bangunan',
            'activeTab' => 'input',
            'item' => $item,
            'tanahList' => SarprasModel::getTanahList(),
            'asalAnggaranList' => SarprasModel::getAllAsalAnggaran()
        ]);
    }

    public static function ruanganEdit(int $id): void
    {
        $item = SarprasModel::getRuanganById($id);
        if (!$item) {
            header('Location: ' . url('mobile-sarpras/ruangan'));
            exit;
        }
        self::render('ruangan/edit', [
            'pageTitle' => 'Edit Ruangan',
            'activeTab' => 'input',
            'item' => $item,
            'bangunanList' => SarprasModel::getBangunanList(),
            'pegawaiList' => SarprasModel::getPegawaiList(),
            'kelasListByUnit' => SarprasModel::getKelasByUnit()
        ]);
    }

    public static function maintenanceTambah(): void
    {
        $barangTersedia = SarprasModel::getBarangList();
        $ruanganList = SarprasModel::db()->findAll("SELECT id, nama_ruangan FROM sarpras_ruangan WHERE is_active = 1");
        
        self::render('maintenance/tambah', [
            'pageTitle' => 'Input Maintenance',
            'activeTab' => 'input',
            'barang' => $barangTersedia,
            'ruanganList' => $ruanganList
        ]);
    }

    public static function peminjamanList(): void
    {
        self::render('peminjaman/index', [
            'pageTitle' => 'Data Peminjaman',
            'activeTab' => 'peminjaman',
            'peminjaman' => SarprasModel::db()->query("SELECT p.*, b.nama_barang, b.kode_barang FROM sarpras_peminjaman p JOIN sarpras_barang b ON p.barang_id = b.id ORDER BY p.created_at DESC")->fetchAll()
        ]);
    }

    public static function peminjamanTambah(): void
    {
        $db = SarprasModel::db();
        // Fetch all active employees
        $pegawaiList = $db->findAll("SELECT id, nama, gelar FROM pegawai WHERE is_active = 1 ORDER BY nama ASC");
        
        // Fetch items available for borrowing along with their room
        $barangTersedia = $db->findAll("SELECT b.id, b.kode_barang, b.nama_barang, r.nama_ruangan FROM sarpras_barang b LEFT JOIN sarpras_ruangan r ON b.ruangan_id = r.id WHERE b.status = 'Tersedia' ORDER BY b.nama_barang ASC");
        
        self::render('peminjaman/tambah', [
            'pageTitle' => 'Input Peminjaman',
            'activeTab' => 'input',
            'pegawaiList' => $pegawaiList,
            'barang' => $barangTersedia
        ]);
    }

    public static function pengajuanTambah(): void
    {
        self::render('pengajuan/tambah', [
            'pageTitle' => 'Pengajuan Pembelian',
            'activeTab' => 'input'
        ]);
    }

    public static function maintenanceStore(): void
    {
        $db = SarprasModel::db();
        $status = $_POST['status'] ?? 'Menunggu';
        
        $db->query("INSERT INTO sarpras_maintenance (barang_id, tanggal_lapor, dilaporkan_oleh, deskripsi_kerusakan, status, biaya, keterangan) VALUES (?, ?, ?, ?, ?, ?, ?)", [
            $_POST['barang_id'],
            $_POST['tanggal_lapor'] ?? date('Y-m-d'),
            $_POST['dilaporkan_oleh'],
            $_POST['deskripsi_kerusakan'],
            $status,
            0,
            $_POST['keterangan'] ?? ''
        ]);
        
        // Update barang status
        $barangStatus = 'Tersedia';
        if ($status == 'Menunggu' || $status == 'Dalam Perbaikan') {
            $barangStatus = 'Dalam Perbaikan';
        }
        $db->query("UPDATE sarpras_barang SET status = ? WHERE id = ?", [$barangStatus, $_POST['barang_id']]);
        
        $_SESSION['flash_success'] = 'Laporan perbaikan berhasil disubmit!';
        header('Location: ' . url('mobile-sarpras/maintenance'));
        exit;
    }

    public static function peminjamanStore(): void
    {
        $db = SarprasModel::db();
        
        // Generate kode pinjam
        $kodePinjam = 'PINJAM-' . date('Ymd-His');
        
        $db->query("INSERT INTO sarpras_peminjaman (kode_pinjam, barang_id, nama_peminjam, jumlah_pinjam, tanggal_pinjam, estimasi_kembali, keperluan, status, kondisi_sebelum) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)", [
            $kodePinjam,
            $_POST['barang_id'],
            $_POST['peminjam'],
            $_POST['jumlah'] ?? 1,
            $_POST['tanggal_pinjam'],
            $_POST['tanggal_rencana_kembali'],
            $_POST['keterangan'] ?? '',
            'Dipinjam',
            'Baik'
        ]);
        
        $db->query("UPDATE sarpras_barang SET status = 'Dipinjam' WHERE id = ?", [$_POST['barang_id']]);
        
        $_SESSION['flash_success'] = 'Peminjaman berhasil dicatat!';
        header('Location: ' . url('mobile-sarpras/peminjaman'));
        exit;
    }

    public static function peminjamanUpdate(int $id): void
    {
        $db = SarprasModel::db();
        $status = trim($_POST['status'] ?? 'Dipinjam');
        $tanggal_pinjam = trim($_POST['tanggal_pinjam'] ?? date('Y-m-d'));
        $estimasi_kembali = trim($_POST['tanggal_rencana_kembali'] ?? date('Y-m-d'));
        
        $pinjam = $db->query("SELECT * FROM sarpras_peminjaman WHERE id = ?", [$id])->fetch();
        
        if ($status === 'Dikembalikan' && $pinjam['status'] === 'Dipinjam') {
            $db->query("UPDATE sarpras_peminjaman SET status = 'Dikembalikan', tanggal_kembali = ?, tanggal_pinjam = ?, estimasi_kembali = ? WHERE id = ?", [
                date('Y-m-d H:i:s'), $tanggal_pinjam, $estimasi_kembali, $id
            ]);
            $db->query("UPDATE sarpras_barang SET status = 'Tersedia' WHERE id = ?", [$pinjam['barang_id']]);
        } elseif ($status === 'Dipinjam' && ($pinjam['status'] === 'Dikembalikan' || $pinjam['status'] === 'Kembali')) {
            $db->query("UPDATE sarpras_peminjaman SET status = 'Dipinjam', tanggal_kembali = NULL, tanggal_pinjam = ?, estimasi_kembali = ? WHERE id = ?", [
                $tanggal_pinjam, $estimasi_kembali, $id
            ]);
            $db->query("UPDATE sarpras_barang SET status = 'Dipinjam' WHERE id = ?", [$pinjam['barang_id']]);
        } else {
            $db->query("UPDATE sarpras_peminjaman SET status = ?, tanggal_pinjam = ?, estimasi_kembali = ? WHERE id = ?", [
                $status, $tanggal_pinjam, $estimasi_kembali, $id
            ]);
        }
        
        $_SESSION['flash_success'] = 'Peminjaman berhasil diperbarui!';
        $redirect = $_POST['redirect_to'] ?? url('mobile-sarpras/peminjaman');
        header('Location: ' . $redirect);
        exit;
    }

    public static function pengajuanStore(): void
    {
        $db = SarprasModel::db();
        $db->query("INSERT INTO sarpras_pengajuan (nama_barang, spesifikasi, jumlah, estimasi_harga, keperluan, pengaju, tanggal_pengajuan, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", [
            $_POST['nama_barang'],
            $_POST['spesifikasi'] ?? '',
            $_POST['jumlah'] ?? 1,
            str_replace(['.', ','], ['', '.'], $_POST['estimasi_harga'] ?? '0'),
            $_POST['keperluan'],
            $_POST['pengaju'],
            date('Y-m-d'),
            'Diajukan'
        ]);
        
        $_SESSION['flash_success'] = 'Pengajuan pembelian berhasil dikirim!';
        header('Location: ' . url('mobile-sarpras'));
        exit;
    }

    public static function subscribePush(): void
    {
        // Handle web push subscription saving logic here
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input && isset($input['endpoint'])) {
            // Save subscription to DB (usually a user_subscriptions table)
            // For now, return success
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success']);
        }
    }

    public static function apiGetBarangByKode(): void
    {
        header('Content-Type: application/json');
        $kode = $_GET['kode'] ?? '';
        $db = SarprasModel::db();
        $barang = $db->find("SELECT b.*, r.nama_ruangan FROM sarpras_barang b LEFT JOIN sarpras_ruangan r ON b.ruangan_id = r.id WHERE b.kode_barang = ?", [$kode]);
        
        if ($barang) {
            echo json_encode(['status' => 'success', 'data' => $barang]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Barang tidak ditemukan']);
        }
        exit;
    }

    public static function apiGetBarangByRuangan(): void
    {
        header('Content-Type: application/json');
        $ruangan_id = $_GET['ruangan_id'] ?? '';
        $db = SarprasModel::db();
        $barang = $db->findAll("SELECT id, kode_barang, nama_barang FROM sarpras_barang WHERE ruangan_id = ?", [$ruangan_id]);
        
        echo json_encode(['status' => 'success', 'data' => $barang]);
        exit;
    }
}

