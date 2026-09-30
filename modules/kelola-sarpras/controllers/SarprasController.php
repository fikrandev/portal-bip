<?php
/**
 * Controller Modul Kelola Sarpras (Sarana & Prasarana)
 * Portal BIP
 */

require_once BASE_PATH . '/modules/kelola-sarpras/models/SarprasModel.php';

class SarprasController
{
    private static function view(string $viewPath, array $data = [], string $pageTitle = 'Kelola Sarpras', array $breadcrumbs = []): void
    {
        extract($data);
        $customSidebar = BASE_PATH . '/modules/kelola-sarpras/views/sidebar.php';

        ob_start();
        include BASE_PATH . '/modules/kelola-sarpras/views/' . $viewPath . '.php';
        $content = ob_get_clean();

        include TEMPLATES_PATH . '/layouts/app.php';
    }

    // ── DASHBOARD SARPRAS ──────────────────────────────────────────
    public static function index(): void
    {
        $stats = SarprasModel::getDashboardStats();
        $recentBarang = SarprasModel::getBarangList([], 5, 0);
        $activePinjam = SarprasModel::getPeminjamanList('Dipinjam');
        $activeMnt = SarprasModel::getPemeliharaanList('Diproses');
        if (empty($activeMnt)) {
            $activeMnt = SarprasModel::getPemeliharaanList('Menunggu');
        }

        self::view('index', [
            'stats' => $stats,
            'recentBarang' => $recentBarang,
            'activePinjam' => $activePinjam,
            'activeMnt' => $activeMnt
        ], 'Dashboard Kelola Sarpras', [
            ['label' => 'Sarana & Prasarana', 'url' => url('kelola-sarpras')],
            ['label' => 'Dashboard']
        ]);
    }

    // ── DAFTAR INVENTARIS BARANG ───────────────────────────────────
    public static function barangList(): void
    {
        $filters = [
            'unit' => trim($_GET['unit'] ?? ''),
            'kategori_id' => trim($_GET['kategori_id'] ?? ''),
            'ruangan_id' => trim($_GET['ruangan_id'] ?? ''),
            'kondisi' => trim($_GET['kondisi'] ?? ''),
            'status' => trim($_GET['status'] ?? ''),
            'search' => trim($_GET['search'] ?? '')
        ];

        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $total = SarprasModel::getBarangCount($filters);
        $items = SarprasModel::getBarangList($filters, $limit, $offset);
        $totalPages = ceil($total / $limit);

        $kategoriList = SarprasModel::getAllKategori();
        $ruanganList = SarprasModel::getAllRuangan();
        $golonganList = SarprasModel::getAllGolongan();
        $kelompokList = SarprasModel::getAllKelompok();
        $asalAnggaranList = SarprasModel::getAllAsalAnggaran();
        $satuanList = SarprasModel::getAllSatuan();

        self::view('barang/index', [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'filters' => $filters,
            'kategoriList' => $kategoriList,
            'ruanganList' => $ruanganList,
            'golonganList' => $golonganList,
            'kelompokList' => $kelompokList,
            'asalAnggaranList' => $asalAnggaranList,
            'satuanList' => $satuanList
        ], 'Inventaris Aset Sarpras', [
            ['label' => 'Sarpras', 'url' => url('kelola-sarpras')],
            ['label' => 'Inventaris Barang']
        ]);
    }

    public static function barangCreate(): void
    {
        $kategoriList = SarprasModel::getAllKategori();
        $ruanganList = SarprasModel::getAllRuangan();
        $golonganList = SarprasModel::getAllGolongan();
        $kelompokList = SarprasModel::getAllKelompok();
        $asalAnggaranList = SarprasModel::getAllAsalAnggaran();

        self::view('barang/create', [
            'kategoriList' => $kategoriList,
            'ruanganList' => $ruanganList,
            'golonganList' => $golonganList,
            'kelompokList' => $kelompokList,
            'asalAnggaranList' => $asalAnggaranList
        ], 'Tambah Aset Sarpras Baru', [
            ['label' => 'Sarpras', 'url' => url('kelola-sarpras')],
            ['label' => 'Inventaris', 'url' => url('kelola-sarpras/barang')],
            ['label' => 'Tambah Barang']
        ]);
    }

    public static function barangStore(): void
    {
        CSRF::validate();

        $namaBarang = trim($_POST['nama_barang'] ?? '');
        $golonganId = (int)($_POST['golongan_id'] ?? 0);
        $kelompokId = (int)($_POST['kelompok_id'] ?? 0);
        $asalAnggaranId = (int)($_POST['asal_anggaran_id'] ?? 0);
        $kategoriId = (int)($_POST['kategori_id'] ?? 1); // fallback
        $ruanganId = (int)($_POST['ruangan_id'] ?? 1); // fallback
        $unit = trim($_POST['unit'] ?? 'SD');
        $jumlah = max(1, (int)($_POST['jumlah'] ?? 1));
        $satuan = trim($_POST['satuan'] ?? 'Unit');
        $kondisi = trim($_POST['kondisi'] ?? 'Baik');
        $status = trim($_POST['status'] ?? 'Tersedia');
        $merkModel = trim($_POST['merk_model'] ?? '');
        $nomorSeri = trim($_POST['nomor_seri'] ?? '');
        $tanggalPerolehan = trim($_POST['tanggal_perolehan'] ?? date('Y-m-d'));
        $masaManfaat = (int)($_POST['masa_manfaat'] ?? 0);
        $hargaRaw = $_POST['harga_perolehan'] ?? '0';
        if (preg_match('/^\d+(\.\d+)?$/', $hargaRaw)) {
            $harga = (float)$hargaRaw;
        } else {
            $harga = (float)str_replace(['.', ','], ['', '.'], $hargaRaw);
        }
        $keterangan = trim($_POST['keterangan'] ?? '');

        if (empty($namaBarang) || empty($golonganId) || empty($kelompokId) || empty($asalAnggaranId)) {
            $_SESSION['flash_error'] = 'Nama barang, Golongan, Kelompok, dan Asal Anggaran wajib diisi!';
            Response::redirect(url('kelola-sarpras/barang/create'));
            return;
        }

        // Generate Kode Barang
        $kodeBarang = SarprasModel::generateKodeBarangCustom($golonganId, $kelompokId, $asalAnggaranId);

        // Upload foto jika ada
        $fotoPath = null;
        if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = BASE_PATH . '/public/uploads/sarpras/';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0755, true);
            }
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $fotoName = 'sarpras_' . time() . '_' . substr(md5(uniqid()), 0, 6) . '.' . $ext;
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $fotoName)) {
                    $fotoPath = 'uploads/sarpras/' . $fotoName;
                }
            }
        }

        try {
            SarprasModel::insertBarang([
                'kode_barang' => $kodeBarang,
                'nama_barang' => $namaBarang,
                'golongan_id' => $golonganId,
                'kelompok_id' => $kelompokId,
                'asal_anggaran_id' => $asalAnggaranId,
                'kategori_id' => $kategoriId,
                'ruangan_id' => $ruanganId,
                'unit' => $unit,
                'merk_model' => $merkModel ?: null,
                'nomor_seri' => $nomorSeri ?: null,
                'jumlah' => $jumlah,
                'satuan' => $satuan,
                'kondisi' => $kondisi,
                'status' => $status,
                'tanggal_perolehan' => $tanggalPerolehan,
                'masa_manfaat' => $masaManfaat,
                'harga_perolehan' => $harga,
                'foto' => $fotoPath,
                'keterangan' => $keterangan ?: null
            ]);

            $_SESSION['flash_success'] = "Data aset '{$namaBarang}' ({$kodeBarang}) berhasil ditambahkan!";
            $redirectUrl = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : url('kelola-sarpras/barang');
            Response::redirect($redirectUrl);
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Gagal menyimpan barang: ' . $e->getMessage();
            $redirectUrl = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : url('kelola-sarpras/barang/create');
            Response::redirect($redirectUrl);
        }
    }

    public static function barangEdit(int $id): void
    {
        $barang = SarprasModel::getBarangById($id);
        if (!$barang) {
            $_SESSION['flash_error'] = 'Data barang tidak ditemukan!';
            Response::redirect(url('kelola-sarpras/barang'));
            return;
        }

        $kategoriList = SarprasModel::getAllKategori();
        $ruanganList = SarprasModel::getAllRuangan();
        $golonganList = SarprasModel::getAllGolongan();
        $kelompokList = SarprasModel::getAllKelompok();
        $asalAnggaranList = SarprasModel::getAllAsalAnggaran();

        self::view('barang/edit', [
            'barang' => $barang,
            'kategoriList' => $kategoriList,
            'ruanganList' => $ruanganList,
            'golonganList' => $golonganList,
            'kelompokList' => $kelompokList,
            'asalAnggaranList' => $asalAnggaranList
        ], 'Edit Aset Sarpras: ' . $barang['nama_barang'], [
            ['label' => 'Sarpras', 'url' => url('kelola-sarpras')],
            ['label' => 'Inventaris', 'url' => url('kelola-sarpras/barang')],
            ['label' => 'Edit Barang']
        ]);
    }

    public static function barangUpdate(int $id): void
    {
        CSRF::validate();

        $barang = SarprasModel::getBarangById($id);
        if (!$barang) {
            $_SESSION['flash_error'] = 'Barang tidak ditemukan!';
            Response::redirect(url('kelola-sarpras/barang'));
            return;
        }

        $namaBarang = trim($_POST['nama_barang'] ?? '');
        $golonganId = (int)($_POST['golongan_id'] ?? 0);
        $kelompokId = (int)($_POST['kelompok_id'] ?? 0);
        $asalAnggaranId = (int)($_POST['asal_anggaran_id'] ?? 0);
        $kategoriId = (int)($_POST['kategori_id'] ?? 1);
        $ruanganId = (int)($_POST['ruangan_id'] ?? 1);
        $unit = trim($_POST['unit'] ?? 'SD');
        $jumlah = max(1, (int)($_POST['jumlah'] ?? 1));
        $satuan = trim($_POST['satuan'] ?? 'Unit');
        $kondisi = trim($_POST['kondisi'] ?? 'Baik');
        $status = trim($_POST['status'] ?? 'Tersedia');
        $merkModel = trim($_POST['merk_model'] ?? '');
        $nomorSeri = trim($_POST['nomor_seri'] ?? '');
        $tanggalPerolehan = trim($_POST['tanggal_perolehan'] ?? date('Y-m-d'));
        $masaManfaat = (int)($_POST['masa_manfaat'] ?? 0);
        $hargaRaw = $_POST['harga_perolehan'] ?? '0';
        if (preg_match('/^\d+(\.\d+)?$/', $hargaRaw)) {
            $harga = (float)$hargaRaw;
        } else {
            $harga = (float)str_replace(['.', ','], ['', '.'], $hargaRaw);
        }
        $keterangan = trim($_POST['keterangan'] ?? '');

        $fotoPath = $barang['foto'];
        if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = BASE_PATH . '/public/uploads/sarpras/';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0755, true);
            }
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $fotoName = 'sarpras_' . time() . '_' . substr(md5(uniqid()), 0, 6) . '.' . $ext;
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $fotoName)) {
                    $fotoPath = 'uploads/sarpras/' . $fotoName;
                }
            }
        }

        SarprasModel::updateBarang($id, [
            'nama_barang' => $namaBarang,
            'golongan_id' => $golonganId,
            'kelompok_id' => $kelompokId,
            'asal_anggaran_id' => $asalAnggaranId,
            'kategori_id' => $kategoriId,
            'ruangan_id' => $ruanganId,
            'unit' => $unit,
            'merk_model' => $merkModel ?: null,
            'nomor_seri' => $nomorSeri ?: null,
            'jumlah' => $jumlah,
            'satuan' => $satuan,
            'kondisi' => $kondisi,
            'status' => $status,
            'tanggal_perolehan' => $tanggalPerolehan,
            'masa_manfaat' => $masaManfaat,
            'harga_perolehan' => $harga,
            'foto' => $fotoPath,
            'keterangan' => $keterangan ?: null
        ]);

        $_SESSION['flash_success'] = "Data aset '{$namaBarang}' berhasil diperbarui!";
        Response::redirect(url('kelola-sarpras/barang'));
    }

    public static function barangDelete(int $id): void
    {
        CSRF::validate();
        $barang = SarprasModel::getBarangById($id);
        if ($barang) {
            SarprasModel::deleteBarang($id);
            $_SESSION['flash_success'] = "Aset '{$barang['nama_barang']}' berhasil dihapus!";
        }
        Response::redirect(url('kelola-sarpras/barang'));
    }

    // ── SIRKULASI PEMINJAMAN ───────────────────────────────────────
    public static function peminjamanList(): void
    {
        $status = trim($_GET['status'] ?? '');
        $items = SarprasModel::getPeminjamanList($status ?: null);
        $barangTersedia = SarprasModel::getBarangList(['status' => 'Tersedia'], 100, 0);

        self::view('peminjaman/index', [
            'items' => $items,
            'status' => $status,
            'barangTersedia' => $barangTersedia
        ], 'Sirkulasi Peminjaman Sarpras', [
            ['label' => 'Sarpras', 'url' => url('kelola-sarpras')],
            ['label' => 'Peminjaman']
        ]);
    }

    public static function peminjamanStore(): void
    {
        CSRF::validate();

        $barangId = (int)($_POST['barang_id'] ?? 0);
        $namaPeminjam = trim($_POST['nama_peminjam'] ?? '');
        $rolePeminjam = trim($_POST['role_peminjam'] ?? 'Guru');
        $kontak = trim($_POST['kontak_peminjam'] ?? '');
        $jumlah = max(1, (int)($_POST['jumlah_pinjam'] ?? 1));
        $tanggalPinjam = trim($_POST['tanggal_pinjam'] ?? date('Y-m-d'));
        $estimasiKembali = trim($_POST['estimasi_kembali'] ?? date('Y-m-d'));
        $keperluan = trim($_POST['keperluan'] ?? '');

        if (!$barangId || empty($namaPeminjam) || empty($keperluan)) {
            $_SESSION['flash_error'] = 'Barang, nama peminjam, dan keperluan wajib diisi!';
            Response::redirect(url('kelola-sarpras/peminjaman'));
            return;
        }

        $kodePinjam = 'PINJAM-' . date('Ymd') . '-' . substr(strtoupper(uniqid()), -4);

        SarprasModel::insertPeminjaman([
            'kode_pinjam' => $kodePinjam,
            'barang_id' => $barangId,
            'nama_peminjam' => $namaPeminjam,
            'role_peminjam' => $rolePeminjam,
            'kontak_peminjam' => $kontak,
            'jumlah_pinjam' => $jumlah,
            'tanggal_pinjam' => $tanggalPinjam,
            'estimasi_kembali' => $estimasiKembali,
            'keperluan' => $keperluan,
            'kondisi_sebelum' => 'Baik',
            'status' => 'Dipinjam',
            'petugas_nama' => Auth::name()
        ]);

        $_SESSION['flash_success'] = "Peminjaman barang berhasil dicatat ({$kodePinjam})!";
        Response::redirect(url('kelola-sarpras/peminjaman'));
    }

    public static function peminjamanKembali(int $id): void
    {
        CSRF::validate();
        $kondisi = trim($_POST['kondisi_sesudah'] ?? 'Baik');
        $catatan = trim($_POST['catatan'] ?? '');

        SarprasModel::kembalikanBarang($id, $kondisi, $catatan);
        $_SESSION['flash_success'] = "Barang telah berhasil dikembalikan!";
        Response::redirect(url('kelola-sarpras/peminjaman'));
    }

    // ── PEMELIHARAAN & PERBAIKAN ───────────────────────────────────
    public static function pemeliharaanList(): void
    {
        $status = trim($_GET['status'] ?? '');
        $items = SarprasModel::getPemeliharaanList($status ?: null);
        $barangList = SarprasModel::getBarangList([], 200, 0);
        $ruanganList = SarprasModel::getAllRuangan();

        self::view('pemeliharaan/index', [
            'items' => $items,
            'status' => $status,
            'barangList' => $barangList,
            'ruanganList' => $ruanganList
        ], 'Pemeliharaan & Perbaikan Sarpras', [
            ['label' => 'Sarpras', 'url' => url('kelola-sarpras')],
            ['label' => 'Pemeliharaan']
        ]);
    }

    public static function pemeliharaanStore(): void
    {
        CSRF::validate();

        $judul = trim($_POST['judul_laporan'] ?? '');
        $pelapor = trim($_POST['pelapor_nama'] ?? Auth::name());
        $barangId = !empty($_POST['barang_id']) ? (int)$_POST['barang_id'] : null;
        $ruanganId = !empty($_POST['ruangan_id']) ? (int)$_POST['ruangan_id'] : null;
        $urgensi = trim($_POST['tingkat_urgensi'] ?? 'Sedang');
        $deskripsi = trim($_POST['deskripsi_kerusakan'] ?? '');
        $estimasiBiaya = (float)str_replace(['.', ','], ['', '.'], $_POST['estimasi_biaya'] ?? '0');

        if (empty($judul) || empty($deskripsi)) {
            $_SESSION['flash_error'] = 'Judul laporan dan rincian kerusakan wajib diisi!';
            Response::redirect(url('kelola-sarpras/pemeliharaan'));
            return;
        }

        $kodeMnt = 'MNT-' . date('Ymd') . '-' . substr(strtoupper(uniqid()), -4);

        SarprasModel::insertPemeliharaan([
            'kode_perbaikan' => $kodeMnt,
            'barang_id' => $barangId,
            'ruangan_id' => $ruanganId,
            'judul_laporan' => $judul,
            'pelapor_nama' => $pelapor,
            'tanggal_lapor' => date('Y-m-d'),
            'tingkat_urgensi' => $urgensi,
            'deskripsi_kerusakan' => $deskripsi,
            'status' => 'Menunggu',
            'estimasi_biaya' => $estimasiBiaya
        ]);

        $_SESSION['flash_success'] = "Laporan pengaduan kerusakan berhasil diajukan ({$kodeMnt})!";
        Response::redirect(url('kelola-sarpras/pemeliharaan'));
    }

    public static function pemeliharaanUpdate(int $id): void
    {
        CSRF::validate();

        $status = trim($_POST['status'] ?? 'Diproses');
        $tindakan = trim($_POST['tindakan_perbaikan'] ?? '');
        $teknisi = trim($_POST['teknisi_pihak'] ?? '');
        $biaya = (float)str_replace(['.', ','], ['', '.'], $_POST['biaya_realisasi'] ?? '0');

        SarprasModel::updateStatusPemeliharaan($id, $status, $tindakan, $biaya, $teknisi);
        $_SESSION['flash_success'] = "Status pemeliharaan/perbaikan berhasil diperbarui!";
        Response::redirect(url('kelola-sarpras/pemeliharaan'));
    }

    // ── MASTER RUANGAN ─────────────────────────────────────────────
    public static function ruanganList(): void
    {
        $items = SarprasModel::getAllRuangan();
        $bangunanList = SarprasModel::getBangunanList();
        $pegawaiList = SarprasModel::getPegawaiList();

        self::view('ruangan/index', [
            'items' => $items,
            'bangunanList' => $bangunanList,
            'pegawaiList' => $pegawaiList
        ], 'Master Ruangan & Gedung', [
            ['label' => 'Sarpras', 'url' => url('kelola-sarpras')],
            ['label' => 'Data Ruangan']
        ]);
    }

    public static function ruanganStore(): void
    {
        CSRF::validate();

        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $nama = trim($_POST['nama_ruangan'] ?? '');
        $kode = trim($_POST['kode_ruangan'] ?? '');
        $jenis = trim($_POST['jenis_ruangan'] ?? 'Ruang Kelas');
        $unit = trim($_POST['unit'] ?? 'Semua');
        $lokasi = trim($_POST['lokasi_gedung'] ?? 'Gedung Utama');
        $pj = trim($_POST['penanggung_jawab'] ?? '');
        $kapasitas = (int)($_POST['kapasitas'] ?? 0);
        $bangunanId = !empty($_POST['bangunan_id']) ? (int)$_POST['bangunan_id'] : null;
        $lantai = max(1, (int)($_POST['lantai'] ?? 1));
        $keterangan = trim($_POST['keterangan'] ?? '') ?: null;

        $panjang = (float)str_replace(',', '.', $_POST['panjang'] ?? '0');
        $lebar = (float)str_replace(',', '.', $_POST['lebar'] ?? '0');
        $luas = (float)str_replace(',', '.', $_POST['luas'] ?? '0');
        if ($luas <= 0 && $panjang > 0 && $lebar > 0) {
            $luas = $panjang * $lebar;
        }

        $bgn = null;
        if ($bangunanId) {
            $bgn = SarprasModel::getBangunanById($bangunanId);
            if ($bgn) {
                $lokasi = $bgn['nama_bangunan'] . ' (Lt. ' . $lantai . ')';
            }
        }

        if (empty($nama)) {
            $_SESSION['flash_error'] = 'Nama ruangan wajib diisi!';
            $redirectUrl = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : (!empty($_POST['return_to']) ? $_POST['return_to'] : url('kelola-sarpras/ruangan'));
            Response::redirect($redirectUrl);
            return;
        }

        if (empty($kode)) {
            $prefix = $bangunanId ? 'R-B' . $bangunanId . '-' : 'R-';
            $kode = $prefix . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $nama), 0, 5)) . '-' . rand(10, 99);
        }

        $data = [
            'bangunan_id' => $bangunanId,
            'kode_ruangan' => $kode,
            'nama_ruangan' => $nama,
            'jenis_ruangan' => $jenis,
            'lantai' => $lantai,
            'unit' => $unit,
            'lokasi_gedung' => $lokasi,
            'penanggung_jawab' => $pj ?: null,
            'kapasitas' => $kapasitas,
            'panjang' => $panjang,
            'lebar' => $lebar,
            'luas' => $luas,
            'keterangan' => $keterangan
        ];

        if ($id) {
            SarprasModel::updateRuangan($id, $data);
            $_SESSION['flash_success'] = "Ruangan '{$nama}' berhasil diperbarui!";
        } else {
            SarprasModel::insertRuangan($data);
            $msgSuffix = $bgn ? " di Gedung '{$bgn['nama_bangunan']}'" : "";
            $_SESSION['flash_success'] = "Ruangan '{$nama}' berhasil ditambahkan{$msgSuffix}!";
        }

        $redirectUrl = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : (!empty($_POST['return_to']) ? $_POST['return_to'] : url('kelola-sarpras/ruangan'));
        Response::redirect($redirectUrl);
    }

    public static function ruanganDelete(int $id): void
    {
        CSRF::validate();
        SarprasModel::deleteRuangan($id);
        $_SESSION['flash_success'] = 'Ruangan berhasil dihapus!';
        $redirectUrl = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : (!empty($_POST['return_to']) ? $_POST['return_to'] : url('kelola-sarpras/ruangan'));
        Response::redirect($redirectUrl);
    }

    public static function ruanganDetail(int $id): void
    {
        $ruangan = SarprasModel::getRuanganById($id);
        if (!$ruangan) {
            $_SESSION['flash_error'] = 'Ruangan tidak ditemukan.';
            Response::redirect(url('kelola-sarpras/ruangan'));
            return;
        }

        $distribusi = SarprasModel::getDistribusiByRuangan($id);
        
        $allBarang = SarprasModel::getBarangList();
        $barangTersedia = array_filter($allBarang, function($b) {
            return ($b['jumlah'] - ($b['dipakai'] ?? 0)) > 0;
        });

        self::view('ruangan/detail', [
            'ruangan' => $ruangan,
            'distribusi' => $distribusi,
            'barangTersedia' => $barangTersedia
        ], 'Detail Ruangan & Aset: ' . $ruangan['nama_ruangan'], [
            ['label' => 'Sarpras', 'url' => url('kelola-sarpras')],
            ['label' => 'Data Ruangan', 'url' => url('kelola-sarpras/ruangan')],
            ['label' => 'Detail Aset']
        ]);
    }

    public static function distribusiStore(): void
    {
        CSRF::validate();

        $ruanganId = (int)$_POST['ruangan_id'];
        $barangId = (int)$_POST['barang_id'];
        $jumlah = (int)$_POST['jumlah'];
        $kondisi = trim($_POST['kondisi'] ?? 'Baik');
        $keterangan = trim($_POST['keterangan'] ?? '');
        
        $barang = SarprasModel::getBarangById($barangId);
        if (!$barang) {
            $_SESSION['flash_error'] = 'Barang tidak ditemukan!';
            Response::redirect(url("kelola-sarpras/ruangan/detail/{$ruanganId}"));
            return;
        }

        $sisa = $barang['jumlah'] - ($barang['dipakai'] ?? 0);
        if ($jumlah > $sisa) {
            $_SESSION['flash_error'] = 'Stok barang tidak mencukupi! Sisa stok hanya ' . $sisa . ' ' . $barang['satuan'];
            Response::redirect(url("kelola-sarpras/ruangan/detail/{$ruanganId}"));
            return;
        }

        SarprasModel::insertDistribusi([
            'ruangan_id' => $ruanganId,
            'barang_id' => $barangId,
            'jumlah' => $jumlah,
            'kondisi' => $kondisi,
            'keterangan' => $keterangan ?: null,
            'tanggal_distribusi' => date('Y-m-d')
        ]);

        $_SESSION['flash_success'] = 'Barang berhasil ditambahkan ke ruangan!';
        Response::redirect(url("kelola-sarpras/ruangan/detail/{$ruanganId}"));
    }

    public static function distribusiDelete(int $id): void
    {
        CSRF::validate();
        $dist = SarprasModel::getDistribusiById($id);
        if ($dist) {
            SarprasModel::deleteDistribusi($id);
            $_SESSION['flash_success'] = 'Barang berhasil ditarik/dihapus dari ruangan!';
            Response::redirect(url("kelola-sarpras/ruangan/detail/{$dist['ruangan_id']}"));
        } else {
            $_SESSION['flash_error'] = 'Data distribusi tidak ditemukan!';
            Response::redirect(url('kelola-sarpras/ruangan'));
        }
    }

    // ── MASTER KATEGORI ────────────────────────────────────────────
    public static function kategoriList(): void
    {
        $items = SarprasModel::getAllKategori();

        self::view('kategori/index', [
            'items' => $items
        ], 'Master Kategori Sarpras', [
            ['label' => 'Sarpras', 'url' => url('kelola-sarpras')],
            ['label' => 'Kategori Barang']
        ]);
    }

    public static function kategoriStore(): void
    {
        CSRF::validate();

        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $nama = trim($_POST['nama_kategori'] ?? '');
        $kode = trim($_POST['kode_kategori'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');

        if (empty($nama)) {
            $_SESSION['flash_error'] = 'Nama kategori wajib diisi!';
            Response::redirect(url('kelola-sarpras/kategori'));
            return;
        }

        if (empty($kode)) {
            $kode = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $nama), 0, 3));
        }

        $data = [
            'kode_kategori' => $kode,
            'nama_kategori' => $nama,
            'deskripsi' => $deskripsi ?: null
        ];

        if ($id) {
            SarprasModel::updateKategori($id, $data);
            $_SESSION['flash_success'] = "Kategori '{$nama}' berhasil diperbarui!";
        } else {
            SarprasModel::insertKategori($data);
            $_SESSION['flash_success'] = "Kategori '{$nama}' berhasil ditambahkan!";
        }

        Response::redirect(url('kelola-sarpras/kategori'));
    }

    public static function kategoriDelete(int $id): void
    {
        CSRF::validate();
        SarprasModel::deleteKategori($id);
        $_SESSION['flash_success'] = 'Kategori berhasil dihapus!';
        Response::redirect(url('kelola-sarpras/kategori'));
    }

    // ── EXPORT EXCEL & CETAK LABEL ─────────────────────────────────
    public static function exportExcel(): void
    {
        $items = SarprasModel::getBarangList([], 5000, 0);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=Rekap_Inventaris_Sarpras_BIP_' . date('Ymd_His') . '.csv');

        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF"); // UTF-8 BOM

        fputcsv($out, [
            'No', 'Kode Barang', 'Nama Barang', 'Kategori', 'Ruangan / Lokasi', 
            'Unit / Jenjang', 'Merk / Model', 'Nomor Seri', 'Jumlah', 'Satuan', 
            'Kondisi', 'Status', 'Sumber Dana', 'Tahun Pengadaan', 'Harga Satuan (Rp)', 'Total Nilai (Rp)'
        ], ';');

        $no = 1;
        foreach ($items as $row) {
            $totalNilai = $row['jumlah'] * $row['harga_perolehan'];
            fputcsv($out, [
                $no++,
                $row['kode_barang'],
                $row['nama_barang'],
                $row['nama_kategori'] ?? '-',
                $row['nama_ruangan'] ?? '-',
                $row['unit'],
                $row['merk_model'] ?? '-',
                $row['nomor_seri'] ?? '-',
                $row['jumlah'],
                $row['satuan'],
                $row['kondisi'],
                $row['status'],
                $row['sumber_dana'] ?? '-',
                $row['tahun_pengadaan'] ?? '-',
                number_format($row['harga_perolehan'], 0, ',', '.'),
                number_format($totalNilai, 0, ',', '.')
            ], ';');
        }

        fclose($out);
        exit;
    }

    public static function cetakLabel(int $id): void
    {
        $barang = SarprasModel::getBarangById($id);
        if (!$barang) {
            $_SESSION['flash_error'] = 'Barang tidak ditemukan!';
            Response::redirect(url('kelola-sarpras/barang'));
            return;
        }

        include BASE_PATH . '/modules/kelola-sarpras/views/barang/cetak_label.php';
        exit;
    }

    // ── DATA ASET HIERARKI (DIARAHKAN LANGSUNG KE DATA TANAH) ────
    public static function dataAset(): void
    {
        Response::redirect(url('kelola-sarpras/tanah'));
    }

    // ── DATA TANAH (ASET TANAH) ────────────────────────────────────
    public static function tanahList(): void
    {
        $filters = [
            'search' => trim($_GET['search'] ?? ''),
            'status_kepemilikan' => trim($_GET['status_kepemilikan'] ?? '')
        ];

        $items = SarprasModel::getTanahList($filters);
        $allBangunan = SarprasModel::getBangunanList();
        $allRuangan = SarprasModel::getAllRuangan();
        $pegawaiList = SarprasModel::getPegawaiList();

        // Map ruangan by bangunan_id
        $ruanganByBangunan = [];
        foreach ($allRuangan as $r) {
            $bId = (int)($r['bangunan_id'] ?? 0);
            $ruanganByBangunan[$bId][] = $r;
        }

        // Map bangunan by tanah_id & attach ruangan
        $bangunanByTanah = [];
        foreach ($allBangunan as $b) {
            $b['ruangan_list'] = $ruanganByBangunan[$b['id']] ?? [];
            $tId = (int)($b['tanah_id'] ?? 0);
            $bangunanByTanah[$tId][] = $b;
        }

        // Attach bangunan_list to each tanah
        foreach ($items as &$t) {
            $t['bangunan_list'] = $bangunanByTanah[$t['id']] ?? [];
        }
        unset($t);
        
        // Ringkasan untuk KPI card
        $totalBidang = count($items);
        $totalLuas = 0;
        $totalNilai = 0;
        $totalBangunan = 0;
        foreach ($items as $t) {
            $totalLuas += (float)$t['luas'];
            $totalNilai += (float)$t['harga_perolehan'];
            $totalBangunan += (int)$t['total_bangunan'];
        }

        self::view('tanah/index', [
            'items' => $items,
            'filters' => $filters,
            'allBangunan' => $allBangunan,
            'pegawaiList' => $pegawaiList,
            'summary' => [
                'total_bidang' => $totalBidang,
                'total_luas' => $totalLuas,
                'total_nilai' => $totalNilai,
                'total_bangunan' => $totalBangunan
            ]
        ], 'Data Aset Tanah', [
            ['label' => 'Sarpras', 'url' => url('kelola-sarpras')],
            ['label' => 'Data Aset'],
            ['label' => 'Tanah']
        ]);
    }

    public static function tanahStore(): void
    {
        CSRF::validate();

        $nama = trim($_POST['nama_tanah'] ?? '');
        if (empty($nama)) {
            $_SESSION['flash_error'] = 'Nama bidang tanah wajib diisi!';
            Response::redirect(url('kelola-sarpras/tanah'));
            return;
        }

        $panjang = (float)str_replace(',', '.', $_POST['panjang'] ?? '0');
        $lebar = (float)str_replace(',', '.', $_POST['lebar'] ?? '0');
        $luas = (float)str_replace(',', '.', $_POST['luas'] ?? '0');
        if ($luas <= 0 && $panjang > 0 && $lebar > 0) {
            $luas = $panjang * $lebar;
        }

        // Parse harga perolehan (bersihkan format pemisah ribuan)
        $hargaStr = preg_replace('/[^0-9]/', '', $_POST['harga_perolehan'] ?? '0');
        $harga = (float)$hargaStr;

        // Handle multiple uploads gambar sertifikat
        $uploadedImages = [];
        if (!empty($_FILES['gambar_sertifikat']['name'][0])) {
            $uploadDir = BASE_PATH . '/public/uploads/sarpras/sertifikat/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $countFiles = count($_FILES['gambar_sertifikat']['name']);
            for ($i = 0; $i < $countFiles; $i++) {
                if ($_FILES['gambar_sertifikat']['error'][$i] === UPLOAD_ERR_OK) {
                    $tmpName = $_FILES['gambar_sertifikat']['tmp_name'][$i];
                    $ext = strtolower(pathinfo($_FILES['gambar_sertifikat']['name'][$i], PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'pdf'])) {
                        $fileName = 'sertifikat_' . time() . '_' . $i . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                        $dest = $uploadDir . $fileName;
                        if (move_uploaded_file($tmpName, $dest)) {
                            $uploadedImages[] = 'uploads/sarpras/sertifikat/' . $fileName;
                        }
                    }
                }
            }
        }

        $data = [
            'nama_tanah' => $nama,
            'no_sertifikat' => trim($_POST['no_sertifikat'] ?? '') ?: null,
            'status_kepemilikan' => trim($_POST['status_kepemilikan'] ?? 'SHM'),
            'panjang' => $panjang,
            'lebar' => $lebar,
            'luas' => $luas,
            'tahun_perolehan' => !empty($_POST['tahun_perolehan']) ? (int)$_POST['tahun_perolehan'] : null,
            'harga_perolehan' => $harga,
            'alamat_lokasi' => trim($_POST['alamat_lokasi'] ?? '') ?: null,
            'gambar_sertifikat' => !empty($uploadedImages) ? json_encode($uploadedImages) : null,
            'keterangan' => trim($_POST['keterangan'] ?? '') ?: null
        ];

        SarprasModel::insertTanah($data);

        $_SESSION['flash_success'] = "Data Aset Tanah '{$nama}' berhasil ditambahkan!";
        $redirectUrl = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : (!empty($_POST['return_to']) ? $_POST['return_to'] : url('kelola-sarpras/tanah'));
        Response::redirect($redirectUrl);
    }

    public static function tanahUpdate(int $id): void
    {
        CSRF::validate();

        $tanah = SarprasModel::getTanahById($id);
        if (!$tanah) {
            $_SESSION['flash_error'] = 'Data aset tanah tidak ditemukan!';
            Response::redirect(url('kelola-sarpras/tanah'));
            return;
        }

        $nama = trim($_POST['nama_tanah'] ?? '');
        if (empty($nama)) {
            $_SESSION['flash_error'] = 'Nama bidang tanah wajib diisi!';
            Response::redirect(url('kelola-sarpras/tanah'));
            return;
        }

        $panjang = (float)str_replace(',', '.', $_POST['panjang'] ?? '0');
        $lebar = (float)str_replace(',', '.', $_POST['lebar'] ?? '0');
        $luas = (float)str_replace(',', '.', $_POST['luas'] ?? '0');
        if ($luas <= 0 && $panjang > 0 && $lebar > 0) {
            $luas = $panjang * $lebar;
        }

        $hargaStr = preg_replace('/[^0-9]/', '', $_POST['harga_perolehan'] ?? '0');
        $harga = (float)$hargaStr;

        // Ambil sertifikat gambar lama yang masih dipertahankan
        $existingImages = $tanah['gambar_sertifikat_list'] ?? [];
        if (isset($_POST['retained_images']) && is_array($_POST['retained_images'])) {
            $existingImages = array_values(array_intersect($existingImages, $_POST['retained_images']));
        }

        // Upload tambahan gambar baru jika ada
        if (!empty($_FILES['gambar_sertifikat']['name'][0])) {
            $uploadDir = BASE_PATH . '/public/uploads/sarpras/sertifikat/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $countFiles = count($_FILES['gambar_sertifikat']['name']);
            for ($i = 0; $i < $countFiles; $i++) {
                if ($_FILES['gambar_sertifikat']['error'][$i] === UPLOAD_ERR_OK) {
                    $tmpName = $_FILES['gambar_sertifikat']['tmp_name'][$i];
                    $ext = strtolower(pathinfo($_FILES['gambar_sertifikat']['name'][$i], PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'pdf'])) {
                        $fileName = 'sertifikat_' . time() . '_' . $i . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                        $dest = $uploadDir . $fileName;
                        if (move_uploaded_file($tmpName, $dest)) {
                            $existingImages[] = 'uploads/sarpras/sertifikat/' . $fileName;
                        }
                    }
                }
            }
        }

        $data = [
            'nama_tanah' => $nama,
            'no_sertifikat' => trim($_POST['no_sertifikat'] ?? '') ?: null,
            'status_kepemilikan' => trim($_POST['status_kepemilikan'] ?? 'SHM'),
            'panjang' => $panjang,
            'lebar' => $lebar,
            'luas' => $luas,
            'tahun_perolehan' => !empty($_POST['tahun_perolehan']) ? (int)$_POST['tahun_perolehan'] : null,
            'harga_perolehan' => $harga,
            'alamat_lokasi' => trim($_POST['alamat_lokasi'] ?? '') ?: null,
            'gambar_sertifikat' => !empty($existingImages) ? json_encode($existingImages) : null,
            'keterangan' => trim($_POST['keterangan'] ?? '') ?: null
        ];

        SarprasModel::updateTanah($id, $data);

        $_SESSION['flash_success'] = "Data Aset Tanah '{$nama}' berhasil diperbarui!";
        $redirectUrl = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : (!empty($_POST['return_to']) ? $_POST['return_to'] : url('kelola-sarpras/tanah'));
        Response::redirect($redirectUrl);
    }

    public static function tanahDelete(int $id): void
    {
        CSRF::validate();
        $tanah = SarprasModel::getTanahById($id);
        if ($tanah) {
            SarprasModel::deleteTanah($id);
            $_SESSION['flash_success'] = "Data Tanah '{$tanah['nama_tanah']}' berhasil dihapus!";
        } else {
            $_SESSION['flash_error'] = 'Data tanah tidak ditemukan!';
        }
        $redirectUrl = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : (!empty($_POST['return_to']) ? $_POST['return_to'] : url('kelola-sarpras/tanah'));
        Response::redirect($redirectUrl);
    }

    public static function tanahCetakPdf(): void
    {
        $items = SarprasModel::getTanahList();
        
        $totalBidang = count($items);
        $totalLuas = 0;
        $totalNilai = 0;
        $totalBangunan = 0;
        foreach ($items as $t) {
            $totalLuas += (float)$t['luas'];
            $totalNilai += (float)$t['harga_perolehan'];
            $totalBangunan += (int)$t['total_bangunan'];
        }

        include BASE_PATH . '/modules/kelola-sarpras/views/tanah/cetak_pdf.php';
        exit;
    }

    // ── DATA BANGUNAN ──────────────────────────────────────────────
    public static function bangunanList(): void
    {
        $filters = [
            'search' => trim($_GET['search'] ?? ''),
            'tanah_id' => trim($_GET['tanah_id'] ?? ''),
            'kondisi_bangunan' => trim($_GET['kondisi_bangunan'] ?? '')
        ];

        $items = SarprasModel::getBangunanList($filters);
        $tanahList = SarprasModel::getTanahList();
        $ruanganList = SarprasModel::getAllRuangan();
        $pegawaiList = SarprasModel::getPegawaiList();

        $ruanganByBangunan = [];
        foreach ($ruanganList as $r) {
            if (!empty($r['bangunan_id'])) {
                $ruanganByBangunan[$r['bangunan_id']][] = $r;
            }
        }

        self::view('bangunan/index', [
            'items' => $items,
            'tanahList' => $tanahList,
            'filters' => $filters,
            'ruanganByBangunan' => $ruanganByBangunan,
            'pegawaiList' => $pegawaiList
        ], 'Data Aset Bangunan & Gedung', [
            ['label' => 'Sarpras', 'url' => url('kelola-sarpras')],
            ['label' => 'Data Aset'],
            ['label' => 'Bangunan']
        ]);
    }

    public static function bangunanStore(): void
    {
        CSRF::validate();

        $nama = trim($_POST['nama_bangunan'] ?? '');
        $tanahId = !empty($_POST['tanah_id']) ? (int)$_POST['tanah_id'] : null;

        if (empty($nama) || empty($tanahId)) {
            $_SESSION['flash_error'] = 'Nama bangunan dan bidang tanah wajib dipilih!';
            Response::redirect(url('kelola-sarpras/bangunan'));
            return;
        }

        $panjang = (float)str_replace(',', '.', $_POST['panjang'] ?? '0');
        $lebar = (float)str_replace(',', '.', $_POST['lebar'] ?? '0');
        $luas = (float)str_replace(',', '.', $_POST['luas_bangunan'] ?? '0');
        if ($luas <= 0 && $panjang > 0 && $lebar > 0) {
            $luas = $panjang * $lebar;
        }

        $biayaRaw = $_POST['harga_perolehan'] ?? $_POST['biaya_pembangunan'] ?? '0';
        $biayaStr = preg_replace('/[^0-9]/', '', $biayaRaw);
        $biaya = (float)$biayaStr;

        // Foto bangunan upload jika ada
        $fotoPath = null;
        if (!empty($_FILES['foto_bangunan']['name']) && $_FILES['foto_bangunan']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = BASE_PATH . '/public/uploads/sarpras/bangunan/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $ext = strtolower(pathinfo($_FILES['foto_bangunan']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $fileName = 'bgn_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                if (move_uploaded_file($_FILES['foto_bangunan']['tmp_name'], $uploadDir . $fileName)) {
                    $fotoPath = 'uploads/sarpras/bangunan/' . $fileName;
                }
            }
        }

        $data = [
            'tanah_id' => $tanahId,
            'nama_bangunan' => $nama,
            'jumlah_lantai' => max(1, (int)($_POST['jumlah_lantai'] ?? 1)),
            'panjang' => $panjang,
            'lebar' => $lebar,
            'luas_bangunan' => $luas,
            'tahun_dibangun' => !empty($_POST['tahun_dibangun']) ? (int)$_POST['tahun_dibangun'] : null,
            'masa_manfaat' => max(1, (int)($_POST['masa_manfaat'] ?? 20)),
            'kondisi_bangunan' => trim($_POST['kondisi_bangunan'] ?? 'Baik'),
            'sumber_dana' => trim($_POST['sumber_dana'] ?? 'Yayasan'),
            'biaya_pembangunan' => $biaya,
            'foto_bangunan' => $fotoPath,
            'keterangan' => trim($_POST['keterangan'] ?? '') ?: null
        ];

        SarprasModel::insertBangunan($data);

        $_SESSION['flash_success'] = "Bangunan/Gedung '{$nama}' berhasil ditambahkan!";
        
        $redirectUrl = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : (!empty($_POST['return_to']) ? $_POST['return_to'] : url('kelola-sarpras/bangunan'));
        Response::redirect($redirectUrl);
    }

    public static function bangunanUpdate(int $id): void
    {
        CSRF::validate();

        $bangunan = SarprasModel::getBangunanById($id);
        if (!$bangunan) {
            $_SESSION['flash_error'] = 'Data bangunan tidak ditemukan!';
            Response::redirect(url('kelola-sarpras/bangunan'));
            return;
        }

        $nama = trim($_POST['nama_bangunan'] ?? '');
        $tanahId = !empty($_POST['tanah_id']) ? (int)$_POST['tanah_id'] : null;

        if (empty($nama) || empty($tanahId)) {
            $_SESSION['flash_error'] = 'Nama bangunan dan bidang tanah wajib dipilih!';
            Response::redirect(url('kelola-sarpras/bangunan'));
            return;
        }

        $panjang = (float)str_replace(',', '.', $_POST['panjang'] ?? '0');
        $lebar = (float)str_replace(',', '.', $_POST['lebar'] ?? '0');
        $luas = (float)str_replace(',', '.', $_POST['luas_bangunan'] ?? '0');
        if ($luas <= 0 && $panjang > 0 && $lebar > 0) {
            $luas = $panjang * $lebar;
        }

        $biayaRaw = $_POST['harga_perolehan'] ?? $_POST['biaya_pembangunan'] ?? '0';
        $biayaStr = preg_replace('/[^0-9]/', '', $biayaRaw);
        $biaya = (float)$biayaStr;

        $fotoPath = $bangunan['foto_bangunan'];
        if (!empty($_FILES['foto_bangunan']['name']) && $_FILES['foto_bangunan']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = BASE_PATH . '/public/uploads/sarpras/bangunan/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $ext = strtolower(pathinfo($_FILES['foto_bangunan']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $fileName = 'bgn_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                if (move_uploaded_file($_FILES['foto_bangunan']['tmp_name'], $uploadDir . $fileName)) {
                    $fotoPath = 'uploads/sarpras/bangunan/' . $fileName;
                }
            }
        }

        $data = [
            'tanah_id' => $tanahId,
            'nama_bangunan' => $nama,
            'jumlah_lantai' => max(1, (int)($_POST['jumlah_lantai'] ?? 1)),
            'panjang' => $panjang,
            'lebar' => $lebar,
            'luas_bangunan' => $luas,
            'tahun_dibangun' => !empty($_POST['tahun_dibangun']) ? (int)$_POST['tahun_dibangun'] : null,
            'masa_manfaat' => max(1, (int)($_POST['masa_manfaat'] ?? 20)),
            'kondisi_bangunan' => trim($_POST['kondisi_bangunan'] ?? 'Baik'),
            'sumber_dana' => trim($_POST['sumber_dana'] ?? 'Yayasan'),
            'biaya_pembangunan' => $biaya,
            'foto_bangunan' => $fotoPath,
            'keterangan' => trim($_POST['keterangan'] ?? '') ?: null
        ];

        SarprasModel::updateBangunan($id, $data);

        $_SESSION['flash_success'] = "Data Bangunan '{$nama}' berhasil diperbarui!";
        $redirectUrl = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : (!empty($_POST['return_to']) ? $_POST['return_to'] : url('kelola-sarpras/bangunan'));
        Response::redirect($redirectUrl);
    }

    // ── REFERENSI (Golongan, Kode Kelompok, Asal Anggaran) ─────────
    public static function referensiList(): void
    {
        $golonganList    = SarprasModel::getGolonganList();
        $kelompokList    = SarprasModel::getKelompokList();
        $asalAnggaranList = SarprasModel::getAsalAnggaranList();

        self::view('referensi/index', [
            'golonganList'     => $golonganList,
            'kelompokList'     => $kelompokList,
            'asalAnggaranList' => $asalAnggaranList,
        ], 'Referensi Sarpras', [
            ['label' => 'Sarpras', 'url' => url('kelola-sarpras')],
            ['label' => 'Referensi']
        ]);
    }

    // -- Golongan --
    public static function golonganStore(): void
    {
        CSRF::validate();
        $id   = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $kode = strtoupper(trim($_POST['kode'] ?? ''));
        $nama = trim($_POST['nama_golongan'] ?? '');

        if (empty($kode) || empty($nama)) {
            $_SESSION['flash_error'] = 'Kode dan Nama Golongan wajib diisi!';
            Response::redirect(url('kelola-sarpras/referensi') . '?tab=golongan');
            return;
        }

        if ($id) {
            SarprasModel::updateGolongan($id, ['kode' => $kode, 'nama_golongan' => $nama]);
            $_SESSION['flash_success'] = "Golongan '{$nama}' berhasil diperbarui!";
        } else {
            SarprasModel::insertGolongan(['kode' => $kode, 'nama_golongan' => $nama]);
            $_SESSION['flash_success'] = "Golongan '{$nama}' berhasil ditambahkan!";
        }
        Response::redirect(url('kelola-sarpras/referensi') . '?tab=golongan');
    }

    public static function golonganDelete(int $id): void
    {
        CSRF::validate();
        $item = SarprasModel::getGolonganById($id);
        if ($item) {
            SarprasModel::deleteGolongan($id);
            $_SESSION['flash_success'] = "Golongan '{$item['nama_golongan']}' berhasil dihapus!";
        } else {
            $_SESSION['flash_error'] = 'Data golongan tidak ditemukan!';
        }
        Response::redirect(url('kelola-sarpras/referensi') . '?tab=golongan');
    }

    // -- Kode Kelompok --
    public static function kelompokStore(): void
    {
        CSRF::validate();
        $id          = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $golonganId  = (int)($_POST['golongan_id'] ?? 0);
        $kode        = strtoupper(trim($_POST['kode'] ?? ''));
        $nama        = trim($_POST['nama_kelompok'] ?? '');
        $keterangan  = trim($_POST['keterangan'] ?? '') ?: null;

        if (!$golonganId || empty($kode) || empty($nama)) {
            $_SESSION['flash_error'] = 'Golongan, Kode, dan Nama Kelompok wajib diisi!';
            Response::redirect(url('kelola-sarpras/referensi') . '?tab=kelompok');
            return;
        }

        $data = ['golongan_id' => $golonganId, 'kode' => $kode, 'nama_kelompok' => $nama, 'keterangan' => $keterangan];

        if ($id) {
            SarprasModel::updateKelompok($id, $data);
            $_SESSION['flash_success'] = "Kode Kelompok '{$nama}' berhasil diperbarui!";
        } else {
            SarprasModel::insertKelompok($data);
            $_SESSION['flash_success'] = "Kode Kelompok '{$nama}' berhasil ditambahkan!";
        }
        Response::redirect(url('kelola-sarpras/referensi') . '?tab=kelompok');
    }

    public static function kelompokDelete(int $id): void
    {
        CSRF::validate();
        $item = SarprasModel::getKelompokById($id);
        if ($item) {
            SarprasModel::deleteKelompok($id);
            $_SESSION['flash_success'] = "Kode Kelompok '{$item['nama_kelompok']}' berhasil dihapus!";
        } else {
            $_SESSION['flash_error'] = 'Data kode kelompok tidak ditemukan!';
        }
        Response::redirect(url('kelola-sarpras/referensi') . '?tab=kelompok');
    }

    // -- Asal Anggaran --
    public static function asalAnggaranStore(): void
    {
        CSRF::validate();
        $id   = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $kode = strtoupper(trim($_POST['kode'] ?? ''));
        $nama = trim($_POST['nama'] ?? '');

        if (empty($kode) || empty($nama)) {
            $_SESSION['flash_error'] = 'Kode dan Nama Asal Anggaran wajib diisi!';
            Response::redirect(url('kelola-sarpras/referensi') . '?tab=asal-anggaran');
            return;
        }

        if ($id) {
            SarprasModel::updateAsalAnggaran($id, ['kode' => $kode, 'nama' => $nama]);
            $_SESSION['flash_success'] = "Asal Anggaran '{$nama}' berhasil diperbarui!";
        } else {
            SarprasModel::insertAsalAnggaran(['kode' => $kode, 'nama' => $nama]);
            $_SESSION['flash_success'] = "Asal Anggaran '{$nama}' berhasil ditambahkan!";
        }
        Response::redirect(url('kelola-sarpras/referensi') . '?tab=asal-anggaran');
    }

    public static function asalAnggaranDelete(int $id): void
    {
        CSRF::validate();
        $item = SarprasModel::getAsalAnggaranById($id);
        if ($item) {
            SarprasModel::deleteAsalAnggaran($id);
            $_SESSION['flash_success'] = "Asal Anggaran '{$item['nama']}' berhasil dihapus!";
        } else {
            $_SESSION['flash_error'] = 'Data asal anggaran tidak ditemukan!';
        }
        Response::redirect(url('kelola-sarpras/referensi') . '?tab=asal-anggaran');
    }

    public static function bangunanDelete(int $id): void
    {
        CSRF::validate();
        $bangunan = SarprasModel::getBangunanById($id);
        if ($bangunan) {
            SarprasModel::deleteBangunan($id);
            $_SESSION['flash_success'] = "Data Bangunan '{$bangunan['nama_bangunan']}' berhasil dihapus!";
        } else {
            $_SESSION['flash_error'] = 'Data bangunan tidak ditemukan!';
        }
        $redirectUrl = !empty($_POST['return_to']) ? $_POST['return_to'] : url('kelola-sarpras/bangunan');
        Response::redirect($redirectUrl);
    }
}

