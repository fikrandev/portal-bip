<?php
/**
 * CutiController - Controller Modul Kelola Cuti Pegawai
 * Portal BIP
 */

require_once BASE_PATH . '/modules/kelola-cuti/models/CutiModel.php';

class CutiController
{
    /**
     * Halaman Utama Kelola Cuti Pegawai (Multi-Tab & Sidebar Terintegrasi)
     */
    public static function index(): void
    {
        CutiModel::init();

        $activeTab = $_GET['tab'] ?? 'pegawai';
        $allowedTabs = ['pegawai', 'pengajuan', 'approve', 'riwayat', 'log', 'jenis'];
        if (!in_array($activeTab, $allowedTabs, true)) {
            $activeTab = 'pegawai';
        }

        $stats = CutiModel::getSummaryStats();

        // Filter Pegawai
        $pegawaiFilters = [
            'search' => trim($_GET['search_pegawai'] ?? ''),
            'unit_tugas' => trim($_GET['unit_tugas'] ?? ''),
            'jabatan' => trim($_GET['jabatan'] ?? ''),
            'status_pegawai' => trim($_GET['status_pegawai'] ?? '')
        ];
        $pegawaiList = CutiModel::getAllPegawaiCuti($pegawaiFilters);
        $allPegawaiDropdown = CutiModel::getAllPegawaiCuti([]);

        // Master Jenis Cuti
        $jenisCutiList = CutiModel::getAllJenisCuti(false);
        $jenisCutiAktif = CutiModel::getAllJenisCuti(true);

        // Data Pengajuan (Semua)
        $pengajuanFilters = [
            'search' => trim($_GET['search_pengajuan'] ?? ''),
            'status' => trim($_GET['status_pengajuan'] ?? ''),
            'kategori_cuti' => trim($_GET['kategori_cuti'] ?? ''),
            'unit_tugas' => trim($_GET['unit_pengajuan'] ?? '')
        ];
        $pengajuanList = CutiModel::getPengajuanList($pengajuanFilters);

        // Data Approve (Hanya Pending)
        $pendingList = CutiModel::getPengajuanList(['status' => 'Pending']);

        // Riwayat Approve (Disetujui, Ditolak, Dibatalkan)
        $riwayatFilters = [
            'status' => !empty($_GET['status_riwayat']) ? $_GET['status_riwayat'] : ['Disetujui', 'Ditolak', 'Dibatalkan'],
            'search' => trim($_GET['search_riwayat'] ?? ''),
            'order_by' => 'approved_at'
        ];
        $riwayatList = CutiModel::getPengajuanList($riwayatFilters);

        // Log Pengurangan & Mutasi Cuti
        $logFilters = [
            'tipe' => $_GET['tipe_log'] ?? '',
            'pegawai_id' => !empty($_GET['pegawai_log']) ? (int)$_GET['pegawai_log'] : 0,
            'search' => trim($_GET['search_log'] ?? '')
        ];
        $logList = CutiModel::getLogList($logFilters);

        // Dropdown Filter Unit & Jabatan
        $db = Database::getInstance();
        $unitTugasList = $db->findAll("SELECT DISTINCT unit_tugas FROM `pegawai` WHERE is_active = 1 AND unit_tugas IS NOT NULL AND unit_tugas != '' ORDER BY unit_tugas ASC");
        $jabatanList = $db->findAll("SELECT DISTINCT jabatan FROM `pegawai` WHERE is_active = 1 AND jabatan IS NOT NULL AND jabatan != '' ORDER BY jabatan ASC");

        $pageTitle = 'Kelola Cuti Pegawai';
        $breadcrumbs = [
            ['label' => 'Kelola Cuti', 'url' => url('kelola-cuti')],
            ['label' => match($activeTab) {
                'pengajuan' => 'Data Pengajuan Cuti',
                'approve'   => 'Verifikasi & Approve Cuti',
                'riwayat'   => 'Riwayat Approve Cuti',
                'log'       => 'Log Pengurangan & Mutasi Cuti',
                'jenis'     => 'Jenis Cuti (Tahunan & Khusus)',
                default     => 'Data Cuti Pegawai'
            }]
        ];
        $customSidebar = BASE_PATH . '/modules/kelola-cuti/views/sidebar.php';

        ob_start();
        include BASE_PATH . '/modules/kelola-cuti/views/index.php';
        $content = ob_get_clean();

        include TEMPLATES_PATH . '/layouts/app.php';
    }

    /**
     * Simpan Pengajuan Cuti Baru
     */
    public static function storePengajuan(): void
    {
        CSRF::validate();
        $returnTab = $_POST['return_tab'] ?? 'pengajuan';

        try {
            $data = $_POST;

            // Upload file lampiran jika ada
            if (!empty($_FILES['file_lampiran']['name']) && $_FILES['file_lampiran']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['file_lampiran']['name'], PATHINFO_EXTENSION));
                $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];
                if (!in_array($ext, $allowed, true)) {
                    throw new Exception('Format file lampiran harus PDF, JPG, PNG, atau WEBP.');
                }
                $uploadDir = BASE_PATH . '/public/uploads/cuti';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $fileName = 'lampiran_cuti_' . time() . '_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['file_lampiran']['tmp_name'], $uploadDir . '/' . $fileName)) {
                    $data['file_lampiran'] = 'uploads/cuti/' . $fileName;
                }
            }

            $userId = Auth::id();
            $userName = Auth::name() ?: 'Admin';
            CutiModel::createPengajuan($data, $userId, $userName);

            $msg = !empty($data['langsung_approve'])
                ? 'Pengajuan cuti berhasil disimpan dan langsung disetujui (saldo telah disesuaikan).'
                : 'Pengajuan cuti berhasil ditambahkan dan masuk ke antrean Data Approve.';

            Response::redirect(url('kelola-cuti?tab=' . urlencode($returnTab)), $msg, 'success');
        } catch (Throwable $e) {
            Response::redirect(url('kelola-cuti?tab=' . urlencode($returnTab)), $e->getMessage(), 'error');
        }
    }

    /**
     * Proses Approve / Tolak / Batalkan Pengajuan Cuti
     */
    public static function approve(): void
    {
        CSRF::validate();
        $returnTab = $_POST['return_tab'] ?? 'approve';

        try {
            $pengajuanId = (int)($_POST['pengajuan_id'] ?? 0);
            $keputusan = trim($_POST['keputusan'] ?? '');
            $catatan = trim($_POST['catatan_approval'] ?? '');

            $userId = Auth::id();
            $userName = Auth::name() ?: 'Admin';

            CutiModel::approvePengajuan($pengajuanId, $keputusan, $catatan, $userId, $userName);

            $label = match($keputusan) {
                'Disetujui' => 'Pengajuan cuti berhasil DISETUJUI dan tercatat di Log Pengurangan Cuti.',
                'Ditolak'   => 'Pengajuan cuti telah DITOLAK.',
                'Dibatalkan'=> 'Pengajuan cuti telah DIBATALKAN dan saldo telah dikembalikan.',
                default     => 'Status pengajuan cuti berhasil diperbarui.'
            };

            Response::redirect(url('kelola-cuti?tab=' . urlencode($returnTab)), $label, 'success');
        } catch (Throwable $e) {
            Response::redirect(url('kelola-cuti?tab=' . urlencode($returnTab)), $e->getMessage(), 'error');
        }
    }

    /**
     * Simpan / Edit Jenis Cuti (Cuti Tahunan maupun Cuti Khusus)
     */
    public static function storeJenisCuti(): void
    {
        CSRF::validate();
        try {
            $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            CutiModel::saveJenisCuti($_POST, $id);
            $msg = $id ? 'Data jenis cuti berhasil diperbarui.' : 'Jenis Cuti Khusus baru berhasil ditambahkan.';
            Response::redirect(url('kelola-cuti?tab=jenis'), $msg, 'success');
        } catch (Throwable $e) {
            Response::redirect(url('kelola-cuti?tab=jenis'), $e->getMessage(), 'error');
        }
    }

    /**
     * Hapus Jenis Cuti Khusus
     */
    public static function deleteJenisCuti(): void
    {
        CSRF::validate();
        try {
            $id = (int)($_POST['id'] ?? 0);
            CutiModel::deleteJenisCuti($id);
            Response::redirect(url('kelola-cuti?tab=jenis'), 'Jenis cuti khusus berhasil dihapus.', 'success');
        } catch (Throwable $e) {
            Response::redirect(url('kelola-cuti?tab=jenis'), $e->getMessage(), 'error');
        }
    }

    /**
     * Penyesuaian Manual Saldo Cuti Tahunan Pegawai
     */
    public static function adjustSaldo(): void
    {
        CSRF::validate();
        $returnTab = $_POST['return_tab'] ?? 'pegawai';

        try {
            $pegawaiId = (int)($_POST['pegawai_id'] ?? 0);
            $saldoBaru = (int)($_POST['saldo_tahunan'] ?? 0);
            $keterangan = trim($_POST['keterangan'] ?? 'Penyesuaian saldo cuti tahunan oleh Admin');

            if ($pegawaiId <= 0) {
                throw new Exception('Pegawai tidak valid.');
            }

            $userId = Auth::id();
            $userName = Auth::name() ?: 'Admin';
            CutiModel::adjustSaldoPegawai($pegawaiId, $saldoBaru, $keterangan, $userId, $userName);

            Response::redirect(url('kelola-cuti?tab=' . urlencode($returnTab)), 'Saldo Cuti Tahunan pegawai berhasil disesuaikan dan tercatat di Log.', 'success');
        } catch (Throwable $e) {
            Response::redirect(url('kelola-cuti?tab=' . urlencode($returnTab)), $e->getMessage(), 'error');
        }
    }

    /**
     * Trigger Penambahan Bulanan (+1) untuk seluruh pegawai aktif (maksimal 36)
     */
    public static function triggerTambahBulanan(): void
    {
        CSRF::validate();
        $returnTab = $_POST['return_tab'] ?? 'pegawai';

        try {
            $userId = Auth::id();
            $userName = Auth::name() ?: 'Admin';
            $cnt = CutiModel::triggerTambahBulananManual($userId, $userName);

            Response::redirect(
                url('kelola-cuti?tab=' . urlencode($returnTab)),
                "Berhasil menambahkan +1 hari Cuti Tahunan untuk {$cnt} pegawai aktif (batas maksimal 36 hari).",
                'success'
            );
        } catch (Throwable $e) {
            Response::redirect(url('kelola-cuti?tab=' . urlencode($returnTab)), $e->getMessage(), 'error');
        }
    }

    /**
     * Cetak Surat Izin Cuti Pegawai
     */
    public static function cetakSurat(int $id): void
    {
        $cuti = CutiModel::getPengajuanById($id);
        if (!$cuti) {
            Response::redirect(url('kelola-cuti?tab=riwayat'), 'Data pengajuan cuti tidak ditemukan.', 'error');
        }

        include BASE_PATH . '/modules/kelola-cuti/views/cetak_surat.php';
        exit;
    }

    /**
     * Export Data Rekap Cuti Pegawai ke CSV/Excel
     */
    public static function exportCsv(): void
    {
        $rows = CutiModel::getAllPegawaiCuti($_GET);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="Rekap_Cuti_Pegawai_' . date('Ymd_His') . '.csv"');

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM for Excel

        fputcsv($out, [
            'No', 'NIY', 'Nama Pegawai', 'Unit Tugas', 'Jabatan', 'Status Pegawai',
            'Hak Cuti Akumulasi', 'Terpakai (Tahunan)', 'Terpakai (Khusus)', 'Sisa Cuti Tahunan (Maks 36)',
            'Periode Tambah Terakhir', 'Status Hari Ini'
        ]);

        foreach ($rows as $idx => $r) {
            $namaLengkap = $r['nama'] . (!empty($r['gelar']) ? ', ' . $r['gelar'] : '');
            $sisa = (int)$r['saldo_tahunan'];
            $terpakaiTahunan = (int)$r['total_terpakai_tahunan'];
            $hakCuti = min(36, max($sisa + $terpakaiTahunan, (int)$r['total_didapat']));
            fputcsv($out, [
                $idx + 1,
                $r['niy'] ?: '-',
                $namaLengkap,
                $r['unit_tugas'] ?: '-',
                $r['jabatan'] ?: '-',
                $r['status_pegawai'] ?: ($r['status_kerja'] ?: 'Tetap'),
                $hakCuti . ' Hari',
                $terpakaiTahunan . ' Hari',
                (int)$r['total_terpakai_khusus'] . ' Hari',
                $sisa . ' Hari',
                $r['periode_terakhir_tambah'] ?: date('Y-m'),
                ((int)$r['sedang_cuti'] > 0) ? 'Sedang Cuti' : 'Aktif Bekerja'
            ]);
        }

        fclose($out);
        exit;
    }
}
