<?php
/**
 * AbsenPegawaiController - Controller Modul Kelola Absen Pegawai
 * Portal BIP
 */

require_once BASE_PATH . '/modules/kelola-absen-pegawai/models/AbsenPegawaiModel.php';

class AbsenPegawaiController
{
    /**
     * Halaman Utama Kelola Absen Pegawai
     */
    public static function index(): void
    {
        AbsenPegawaiModel::init();

        $activeTab = $_GET['tab'] ?? 'pegawai';
        $allowedTabs = ['pegawai', 'unit', 'pengaturan'];
        if (!in_array($activeTab, $allowedTabs, true)) {
            $activeTab = 'pegawai';
        }

        $stats = AbsenPegawaiModel::getSummaryStats();
        $globalConfig = $stats['globalConfig'];

        $filters = [
            'search' => trim($_GET['search'] ?? ''),
            'unit_tugas' => trim($_GET['unit_tugas'] ?? ''),
            'mode_lokasi' => trim($_GET['mode_lokasi'] ?? '')
        ];

        $pegawaiList = AbsenPegawaiModel::getAllPegawaiLokasi($filters);
        $allPegawaiDropdown = AbsenPegawaiModel::getAllPegawaiLokasi([]);
        $unitLokasiList = AbsenPegawaiModel::getAllUnitLokasi();

        $pageTitle = 'Kelola Absen Pegawai';
        $breadcrumbs = [
            ['label' => 'Kelola Absen Pegawai', 'url' => url('kelola-absen-pegawai')],
            ['label' => match($activeTab) {
                'unit'       => 'Lokasi Custom Per Unit',
                'pengaturan' => 'Radius Global & Lokasi Semula',
                default      => 'Lokasi Absen Per Pegawai'
            }]
        ];
        $customSidebar = BASE_PATH . '/modules/kelola-absen-pegawai/views/sidebar.php';

        ob_start();
        include BASE_PATH . '/modules/kelola-absen-pegawai/views/index.php';
        $content = ob_get_clean();

        include TEMPLATES_PATH . '/layouts/app.php';
    }

    /**
     * Simpan Pengaturan Global (Radius Global untuk semua + Lokasi Semula/Default)
     */
    public static function savePengaturanGlobal(): void
    {
        CSRF::validate();
        $returnTab = $_POST['return_tab'] ?? 'pengaturan';

        try {
            AbsenPegawaiModel::savePengaturanGlobal($_POST, Auth::id());
            $radius = max(1, (int)($_POST['radius_meter'] ?? 10));
            $kantor = trim($_POST['nama_kantor_default'] ?? '');
            Response::redirect(
                url('kelola-absen-pegawai?tab=' . urlencode($returnTab)),
                "Pengaturan berhasil disimpan! Radius Global: {$radius} Meter untuk seluruh pegawai, Lokasi Semula: {$kantor}.",
                'success'
            );
        } catch (Throwable $e) {
            Response::redirect(url('kelola-absen-pegawai?tab=' . urlencode($returnTab)), $e->getMessage(), 'error');
        }
    }

    /**
     * Update Cepat Radius Global (Berlaku untuk Semua Pegawai & Semua Unit)
     */
    public static function updateRadiusQuick(): void
    {
        CSRF::validate();
        $returnTab = $_POST['return_tab'] ?? 'pegawai';

        try {
            $radius = max(1, (int)($_POST['radius_meter'] ?? 10));
            AbsenPegawaiModel::updateRadiusGlobal($radius, Auth::id());
            Response::redirect(
                url('kelola-absen-pegawai?tab=' . urlencode($returnTab)),
                "Radius absen global berhasil diatur menjadi {$radius} Meter untuk seluruh pegawai dan unit.",
                'success'
            );
        } catch (Throwable $e) {
            Response::redirect(url('kelola-absen-pegawai?tab=' . urlencode($returnTab)), $e->getMessage(), 'error');
        }
    }

    /**
     * Simpan Lokasi Absen Custom Per Pegawai (Individu maupun Massal/Terpilih)
     */
    public static function saveLokasiPegawai(): void
    {
        CSRF::validate();
        $returnTab = $_POST['return_tab'] ?? 'pegawai';

        try {
            $pegawaiIds = [];
            if (!empty($_POST['pegawai_ids']) && is_array($_POST['pegawai_ids'])) {
                $pegawaiIds = array_map('intval', $_POST['pegawai_ids']);
            } elseif (!empty($_POST['pegawai_ids_csv'])) {
                $pegawaiIds = array_filter(array_map('intval', explode(',', (string)$_POST['pegawai_ids_csv'])));
            } elseif (!empty($_POST['pegawai_id'])) {
                $pegawaiIds = [(int)$_POST['pegawai_id']];
            }

            $namaKantor = trim($_POST['nama_kantor'] ?? '');
            $lat = (float)($_POST['latitude'] ?? 0);
            $lng = (float)($_POST['longitude'] ?? 0);
            $ket = trim($_POST['keterangan'] ?? '');

            $tipeDurasi = $_POST['tipe_durasi'] ?? 'permanen';
            $tglMulai = null;
            $tglSelesai = null;
            $hariAktif = null;

            if ($tipeDurasi === 'terjadwal') {
                $tglMulai = !empty($_POST['tanggal_mulai']) ? trim($_POST['tanggal_mulai']) : null;
                $tglSelesai = !empty($_POST['tanggal_selesai']) ? trim($_POST['tanggal_selesai']) : null;
                if (!empty($_POST['hari_aktif']) && is_array($_POST['hari_aktif'])) {
                    $hariAktif = implode(', ', array_map('trim', $_POST['hari_aktif']));
                }
            }

            $cnt = AbsenPegawaiModel::saveLokasiPegawai(
                $pegawaiIds,
                $namaKantor,
                $lat,
                $lng,
                $ket,
                Auth::id(),
                $tglMulai,
                $tglSelesai,
                $hariAktif
            );

            $infoJadwal = ($tipeDurasi === 'terjadwal')
                ? ' [Terjadwal: ' . ($hariAktif ?: 'Semua Hari') . ($tglMulai ? " ({$tglMulai} s/d {$tglSelesai})" : '') . ']'
                : ' [Permanen]';

            Response::redirect(
                url('kelola-absen-pegawai?tab=' . urlencode($returnTab)),
                "Lokasi absen custom ({$namaKantor}: {$lat}, {$lng}){$infoJadwal} berhasil disimpan untuk {$cnt} pegawai.",
                'success'
            );
        } catch (Throwable $e) {
            Response::redirect(url('kelola-absen-pegawai?tab=' . urlencode($returnTab)), $e->getMessage(), 'error');
        }
    }

    /**
     * Reset Lokasi Absen Pegawai kembali ke Lokasi Semula
     */
    public static function resetLokasiPegawai(): void
    {
        CSRF::validate();
        $returnTab = $_POST['return_tab'] ?? 'pegawai';

        try {
            $pegawaiIds = [];
            if (!empty($_POST['pegawai_ids_csv'])) {
                $pegawaiIds = array_filter(array_map('intval', explode(',', (string)$_POST['pegawai_ids_csv'])));
            } elseif (!empty($_POST['pegawai_id'])) {
                $pegawaiIds = [(int)$_POST['pegawai_id']];
            }

            if (empty($pegawaiIds)) {
                throw new Exception('Pilih pegawai yang akan di-reset lokasinya.');
            }

            $cnt = AbsenPegawaiModel::resetLokasiPegawai($pegawaiIds);
            Response::redirect(
                url('kelola-absen-pegawai?tab=' . urlencode($returnTab)),
                "Lokasi absen {$cnt} pegawai berhasil di-reset ke lokasi semula.",
                'success'
            );
        } catch (Throwable $e) {
            Response::redirect(url('kelola-absen-pegawai?tab=' . urlencode($returnTab)), $e->getMessage(), 'error');
        }
    }

    /**
     * Simpan Lokasi Absen Custom Per Unit Tugas
     */
    public static function saveLokasiUnit(): void
    {
        CSRF::validate();
        $returnTab = $_POST['return_tab'] ?? 'unit';

        try {
            $unitTugas = trim($_POST['unit_tugas'] ?? '');
            $namaKantor = trim($_POST['nama_kantor'] ?? '');
            $lat = (float)($_POST['latitude'] ?? 0);
            $lng = (float)($_POST['longitude'] ?? 0);
            $overwriteCustom = !empty($_POST['overwrite_custom_pegawai']);

            $cnt = AbsenPegawaiModel::saveLokasiUnit($unitTugas, $namaKantor, $lat, $lng, $overwriteCustom, Auth::id());

            Response::redirect(
                url('kelola-absen-pegawai?tab=' . urlencode($returnTab)),
                "Lokasi absen Unit '{$unitTugas}' ({$namaKantor}) berhasil disimpan dan diterapkan ke {$cnt} pegawai.",
                'success'
            );
        } catch (Throwable $e) {
            Response::redirect(url('kelola-absen-pegawai?tab=' . urlencode($returnTab)), $e->getMessage(), 'error');
        }
    }

    /**
     * Reset Lokasi Absen 1 Unit kembali ke Lokasi Semula (Default)
     */
    public static function resetLokasiUnit(): void
    {
        CSRF::validate();
        $returnTab = $_POST['return_tab'] ?? 'unit';

        try {
            $unitTugas = trim($_POST['unit_tugas'] ?? '');
            if ($unitTugas === '') {
                throw new Exception('Nama unit tidak valid.');
            }
            $cnt = AbsenPegawaiModel::resetLokasiUnit($unitTugas);
            Response::redirect(
                url('kelola-absen-pegawai?tab=' . urlencode($returnTab)),
                "Lokasi absen Unit '{$unitTugas}' ({$cnt} pegawai) berhasil di-reset ke lokasi semula.",
                'success'
            );
        } catch (Throwable $e) {
            Response::redirect(url('kelola-absen-pegawai?tab=' . urlencode($returnTab)), $e->getMessage(), 'error');
        }
    }

    /**
     * Reset SELURUH Lokasi Absen (Semua Pegawai & Semua Unit) ke Lokasi Semula (Default)
     */
    public static function resetSemuaLokasi(): void
    {
        CSRF::validate();
        $returnTab = $_POST['return_tab'] ?? 'pegawai';

        try {
            AbsenPegawaiModel::resetSemuaLokasiKeSemula();
            $global = AbsenPegawaiModel::getPengaturanGlobal();
            Response::redirect(
                url('kelola-absen-pegawai?tab=' . urlencode($returnTab)),
                "Seluruh lokasi absen pegawai dan unit berhasil di-reset kembali ke Lokasi Semula ({$global['nama_kantor_default']}).",
                'success'
            );
        } catch (Throwable $e) {
            Response::redirect(url('kelola-absen-pegawai?tab=' . urlencode($returnTab)), $e->getMessage(), 'error');
        }
    }

    /**
     * Export Daftar Lokasi Absen Seluruh Pegawai ke CSV
     */
    public static function exportCsv(): void
    {
        $rows = AbsenPegawaiModel::getAllPegawaiLokasi($_GET);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="Data_Lokasi_Absen_Pegawai_' . date('Ymd_His') . '.csv"');

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

        fputcsv($out, [
            'No', 'NIY', 'Nama Pegawai', 'Unit Tugas', 'Jabatan',
            'Mode Lokasi', 'Nama Kantor Tempat Absen', 'Latitude', 'Longitude', 'Radius Global (Meter)'
        ]);

        foreach ($rows as $idx => $r) {
            $namaLengkap = $r['nama'] . (!empty($r['gelar']) ? ', ' . $r['gelar'] : '');
            fputcsv($out, [
                $idx + 1,
                $r['niy'] ?: '-',
                $namaLengkap,
                $r['unit_tugas'] ?: 'Umum / Yayasan',
                $r['jabatan'] ?: '-',
                $r['status_lokasi_label'],
                $r['nama_kantor_efektif'],
                $r['latitude_efektif'],
                $r['longitude_efektif'],
                $r['radius_meter'] . ' Meter'
            ]);
        }

        fclose($out);
        exit;
    }
}
