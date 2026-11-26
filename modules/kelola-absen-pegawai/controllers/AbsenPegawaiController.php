<?php
/**
 * AbsenPegawaiController - Controller Modul Kelola Absen Pegawai
 * Portal BIP
 */

require_once BASE_PATH . '/modules/kelola-absen-pegawai/models/AbsenPegawaiModel.php';

// Fallback Class Flash untuk memastikan tidak ada error jika core/Response belum termuat/terupdate
if (!class_exists('Flash')) {
    class Flash
    {
        public static function success(string $message): void { $_SESSION['flash_success'] = $message; }
        public static function error(string $message): void { $_SESSION['flash_error'] = $message; }
        public static function info(string $message): void { $_SESSION['flash_info'] = $message; }
        public static function warning(string $message): void { $_SESSION['flash_warning'] = $message; }
    }
}

class AbsenPegawaiController
{
    /**
     * Halaman Utama Kelola Absen Pegawai
     */
    public static function index(): void
    {
        AbsenPegawaiModel::init();

        $activeTab = $_GET['tab'] ?? 'dashboard';
        $isPersonalScope = class_exists('Auth') && Auth::isPersonalScope();
        $allowedTabs = $isPersonalScope
            ? ['dashboard', 'pegawai', 'rekap']
            : ['dashboard', 'pegawai', 'rekap', 'unit', 'pengaturan', 'libur'];
        if (!in_array($activeTab, $allowedTabs, true)) {
            $activeTab = 'dashboard';
        }

        $effectiveUserUnit = AbsenPegawaiModel::getEffectiveUserUnit();
        $isUnitRestricted = !empty($effectiveUserUnit);

        $stats = AbsenPegawaiModel::getSummaryStats();
        $globalConfig = $stats['globalConfig'];

        $defaultStartDate = ((int)date('j') <= 21)
            ? date('Y-m-22', strtotime('first day of previous month'))
            : date('Y-m-22');
        $defaultPeriodEndDate = ((int)date('j') <= 21)
            ? date('Y-m-21')
            : date('Y-m-21', strtotime('first day of next month'));

        $filters = [
            'search'          => trim($_GET['search'] ?? ''),
            'unit_tugas'      => $isUnitRestricted ? $effectiveUserUnit : trim($_GET['unit_tugas'] ?? ''),
            'mode_lokasi'     => trim($_GET['mode_lokasi'] ?? ''),
            'status'          => trim($_GET['status'] ?? ''),
            'rekap_view'      => trim($_GET['rekap_view'] ?? 'periode'),
            'start_date'      => trim($_GET['start_date'] ?? $defaultStartDate),
            'end_date'        => trim($_GET['end_date'] ?? date('Y-m-d')),
            'matrix_end_date' => trim($_GET['end_date'] ?? $defaultPeriodEndDate),
        ];

        $targetDate = trim($_GET['tanggal'] ?? date('Y-m-d'));
        $rawLiburTahun = trim((string)($_GET['libur_tahun'] ?? ''));
        $liburTahun = ($rawLiburTahun === 'all')
            ? null
            : ($rawLiburTahun !== '' ? (int)$rawLiburTahun : (int)date('Y'));

        // Load data scoped to user unit
        $dashboardData = AbsenPegawaiModel::getDashboardData($targetDate, $filters['unit_tugas']);
        $rekapPeriodeData = AbsenPegawaiModel::getRekapPeriode(
            $filters['start_date'],
            $filters['end_date'],
            $filters['unit_tugas'],
            $filters['search']
        );
        $rekapData = AbsenPegawaiModel::getRekapHarian($targetDate, $filters);
        $rekapMatrixData = ($filters['rekap_view'] === 'matrix')
            ? AbsenPegawaiModel::getRekapMatrix($filters['start_date'], $filters['matrix_end_date'], $filters['unit_tugas'], $filters['search'])
            : null;
        $pegawaiList = AbsenPegawaiModel::getAllPegawaiLokasi($filters);
        $allPegawaiDropdown = AbsenPegawaiModel::getAllPegawaiLokasi([]);
        $unitLokasiList = AbsenPegawaiModel::getAllUnitLokasi();
        $liburList = AbsenPegawaiModel::getLiburList($liburTahun);
        $urutanUnitMap = AbsenPegawaiModel::getUrutanUnitMap();

        $pageTitle = 'Kelola Absen Pegawai';
        $breadcrumbs = [
            ['label' => 'Kelola Absen Pegawai', 'url' => url('kelola-absen-pegawai')],
            ['label' => match($activeTab) {
                'dashboard'  => 'Dashboard & Terlambat Hari Ini',
                'rekap'      => 'Data Absen & Rekap PDF',
                'unit'       => 'Lokasi Custom Per Unit',
                'pengaturan' => 'Radius Global & Jam Masuk',
                'libur'      => 'Pengaturan Hari Libur',
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
     * Cetak Laporan PDF / Print View Rekapitulasi Absensi Pegawai
     */
    public static function cetakPdfRekap(): void
    {
        AbsenPegawaiModel::init();

        $effectiveUserUnit = AbsenPegawaiModel::getEffectiveUserUnit();
        $unit = $effectiveUserUnit ?: trim($_GET['unit_tugas'] ?? '');
        $defaultStartDate = ((int)date('j') <= 21)
            ? date('Y-m-22', strtotime('first day of previous month'))
            : date('Y-m-22');
        $startDate = trim($_GET['start_date'] ?? $defaultStartDate);
        $endDate = trim($_GET['end_date'] ?? date('Y-m-d'));
        $search = trim($_GET['search'] ?? '');

        $rekapPeriodeData = AbsenPegawaiModel::getRekapPeriode($startDate, $endDate, $unit, $search);
        $globalConfig = AbsenPegawaiModel::getPengaturanGlobal();
        $targetUnit = $unit;

        include BASE_PATH . '/modules/kelola-absen-pegawai/views/cetak_pdf.php';
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
            $jamBatas = trim($_POST['jam_masuk_batas'] ?? '07:30:00');
            $sabtuInfo = !empty($_POST['sabtu_khusus_aktif'])
                ? ', Sabtu Khusus: ' . substr(trim($_POST['sabtu_jam_masuk_batas'] ?? '07:30'), 0, 5) . ' WITA'
                : '';
            Response::redirect(
                url('kelola-absen-pegawai?tab=' . urlencode($returnTab)),
                "Pengaturan berhasil disimpan! Radius: {$radius}m, Lokasi: {$kantor}, Batas Jam Masuk (Senin-Jumat): " . substr($jamBatas, 0, 5) . " WITA{$sabtuInfo}.",
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

            $tipeJamKerja = $_POST['tipe_jam_kerja'] ?? 'global';
            $customJamKerja = ($tipeJamKerja === 'custom');
            $jamMasukMulai = $customJamKerja ? trim($_POST['jam_masuk_mulai'] ?? '') : null;
            $jamMasukBatas = $customJamKerja ? trim($_POST['jam_masuk_batas'] ?? '') : null;
            $jamPulangMulai = $customJamKerja ? trim($_POST['jam_pulang_mulai'] ?? '') : null;

            $cnt = AbsenPegawaiModel::saveLokasiPegawai(
                $pegawaiIds,
                $namaKantor,
                $lat,
                $lng,
                $ket,
                Auth::id(),
                $tglMulai,
                $tglSelesai,
                $hariAktif,
                $jamMasukMulai,
                $jamMasukBatas,
                $jamPulangMulai,
                $customJamKerja
            );

            $infoJadwal = ($tipeDurasi === 'terjadwal')
                ? ' [Terjadwal: ' . ($hariAktif ?: 'Semua Hari') . ($tglMulai ? " ({$tglMulai} s/d {$tglSelesai})" : '') . ']'
                : ' [Permanen]';
            $infoJam = $customJamKerja
                ? ' [Jam Khusus: ' . substr($jamMasukBatas, 0, 5) . ' - ' . substr($jamPulangMulai, 0, 5) . ']'
                : '';

            Response::redirect(
                url('kelola-absen-pegawai?tab=' . urlencode($returnTab)),
                "Lokasi absen custom ({$namaKantor}: {$lat}, {$lng}){$infoJadwal}{$infoJam} berhasil disimpan untuk {$cnt} pegawai.",
                'success'
            );
        } catch (Throwable $e) {
            Response::redirect(url('kelola-absen-pegawai?tab=' . urlencode($returnTab)), $e->getMessage(), 'error');
        }
    }

    /**
     * Simpan Jam Kerja Khusus (Kedatangan & Pulang) Per Pegawai / Massal Terpilih
     */
    public static function saveJamKerjaPegawai(): void
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

            if (empty($pegawaiIds)) {
                throw new Exception('Silakan pilih minimal 1 pegawai.');
            }

            $modeJam = $_POST['mode_jam_kerja'] ?? 'custom';
            $resetToGlobal = ($modeJam === 'global');
            $isPerHari = ($modeJam === 'per_hari');
            $jamMasukMulai = trim($_POST['jam_masuk_mulai'] ?? '');
            $jamMasukBatas = trim($_POST['jam_masuk_batas'] ?? '');
            $jamPulangMulai = trim($_POST['jam_pulang_mulai'] ?? '');
            $jamHarian = $isPerHari && isset($_POST['hari_custom']) && is_array($_POST['hari_custom'])
                ? $_POST['hari_custom']
                : null;

            $cnt = AbsenPegawaiModel::saveJamKerjaPegawai(
                $pegawaiIds,
                $jamMasukMulai,
                $jamMasukBatas,
                $jamPulangMulai,
                $resetToGlobal,
                Auth::id(),
                $jamHarian
            );

            if ($resetToGlobal) {
                $msg = "Jam kerja {$cnt} pegawai berhasil di-reset kembali mengikuti Jam Kerja Global (Semua).";
            } elseif ($isPerHari) {
                $dayShort = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
                $parts = [];
                foreach (($jamHarian ?? []) as $hk => $cfg) {
                    if (!empty($cfg['aktif']) && !empty($cfg['jam_masuk_batas'])) {
                        $parts[] = ($dayShort[(int)$hk] ?? "Hari {$hk}") . ': ' . substr(trim((string)$cfg['jam_masuk_batas']), 0, 5);
                    }
                }
                $summary = !empty($parts) ? implode(', ', $parts) : 'Per Hari';
                $msg = "Jadwal jam kerja khusus per hari ({$summary}) berhasil disimpan untuk {$cnt} pegawai.";
            } else {
                $msg = "Jam kerja khusus (Batas Masuk: " . substr($jamMasukBatas, 0, 5) . ", Pulang: " . substr($jamPulangMulai, 0, 5) . ") berhasil disimpan untuk {$cnt} pegawai.";
            }

            Response::redirect(url('kelola-absen-pegawai?tab=' . urlencode($returnTab)), $msg, 'success');
        } catch (Throwable $e) {
            Response::redirect(url('kelola-absen-pegawai?tab=' . urlencode($returnTab)), $e->getMessage(), 'error');
        }
    }

    /**
     * Reset Jam Kerja Pegawai Kembali ke Jam Global
     */
    public static function resetJamKerjaPegawai(): void
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
                throw new Exception('Pilih pegawai yang akan di-reset jam kerjanya.');
            }

            $cnt = AbsenPegawaiModel::saveJamKerjaPegawai($pegawaiIds, null, null, null, true, Auth::id());
            Response::redirect(
                url('kelola-absen-pegawai?tab=' . urlencode($returnTab)),
                "Jam kerja {$cnt} pegawai berhasil dikembalikan ke Jam Global (Semua).",
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

    /**
     * Update log presensi harian pegawai (ubah status tepat waktu / terlambat / izin / sakit dsb)
     */
    public static function updateLogPresensi(): void
    {
        CSRF::validate();
        $returnTab = $_POST['return_tab'] ?? 'rekap';
        $returnDate = $_POST['tanggal'] ?? date('Y-m-d');
        $rekapView = $_POST['rekap_view'] ?? 'harian';
        $redirectUrl = url('kelola-absen-pegawai?tab=' . urlencode($returnTab) . '&tanggal=' . urlencode($returnDate) . '&rekap_view=' . urlencode($rekapView));

        try {
            $pegawaiId = (int)($_POST['pegawai_id'] ?? 0);
            $tanggal = trim((string)($_POST['tanggal'] ?? ''));
            if (!$pegawaiId || empty($tanggal)) {
                Response::redirect($redirectUrl, 'Data pegawai atau tanggal tidak valid.', 'error');
            }

            // Periksa hak akses unit
            $effUnit = AbsenPegawaiModel::getEffectiveUserUnit();
            if ($effUnit !== null) {
                $pegUnit = AbsenPegawaiModel::getPegawaiUnit($pegawaiId);
                if ($pegUnit && strcasecmp($pegUnit, $effUnit) !== 0 && stripos($pegUnit, $effUnit) === false) {
                    Response::redirect($redirectUrl, 'Anda tidak memiliki hak akses untuk mengubah presensi pegawai unit ' . $pegUnit, 'error');
                }
            }

            $success = AbsenPegawaiModel::updateLogPresensi([
                'pegawai_id' => $pegawaiId,
                'tanggal' => $tanggal,
                'status' => $_POST['status'] ?? 'Hadir',
                'jam_masuk' => $_POST['jam_masuk'] ?? null,
                'jam_pulang' => $_POST['jam_pulang'] ?? null,
                'nama_kantor_absen' => $_POST['nama_kantor_absen'] ?? null,
                'keterangan' => $_POST['keterangan'] ?? null,
            ]);

            if ($success) {
                Response::redirect($redirectUrl, 'Log presensi pegawai berhasil diperbarui.', 'success');
            } else {
                Response::redirect($redirectUrl, 'Gagal memperbarui log presensi.', 'error');
            }
        } catch (\Throwable $e) {
            Response::redirect($redirectUrl, 'Terjadi kesalahan: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Hapus / Reset log presensi harian pegawai (kembalikan ke Belum Absen)
     */
    public static function deleteLogPresensi(): void
    {
        CSRF::validate();
        $returnTab = $_POST['return_tab'] ?? 'rekap';
        $returnDate = $_POST['tanggal'] ?? date('Y-m-d');
        $rekapView = $_POST['rekap_view'] ?? 'harian';
        $redirectUrl = url('kelola-absen-pegawai?tab=' . urlencode($returnTab) . '&tanggal=' . urlencode($returnDate) . '&rekap_view=' . urlencode($rekapView));

        try {
            $pegawaiId = (int)($_POST['pegawai_id'] ?? 0);
            $tanggal = trim((string)($_POST['tanggal'] ?? ''));
            if (!$pegawaiId || empty($tanggal)) {
                Response::redirect($redirectUrl, 'Data pegawai atau tanggal tidak valid.', 'error');
            }

            // Periksa hak akses unit
            $effUnit = AbsenPegawaiModel::getEffectiveUserUnit();
            if ($effUnit !== null) {
                $pegUnit = AbsenPegawaiModel::getPegawaiUnit($pegawaiId);
                if ($pegUnit && strcasecmp($pegUnit, $effUnit) !== 0 && stripos($pegUnit, $effUnit) === false) {
                    Response::redirect($redirectUrl, 'Anda tidak memiliki hak akses untuk menghapus log presensi pegawai unit ' . $pegUnit, 'error');
                }
            }

            $success = AbsenPegawaiModel::deleteLogPresensi($pegawaiId, $tanggal);
            if ($success) {
                Response::redirect($redirectUrl, 'Log presensi pegawai berhasil dihapus (status kembali ke Belum Absen).', 'success');
            } else {
                Response::redirect($redirectUrl, 'Gagal menghapus log presensi.', 'error');
            }
        } catch (\Throwable $e) {
            Response::redirect($redirectUrl, 'Terjadi kesalahan: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Simpan Catatan Keterlambatan (Evaluasi & Tindak Lanjut)
     */
    public static function saveCatatanTerlambat(): void
    {
        CSRF::validate();
        $returnTab = $_POST['return_tab'] ?? 'dashboard';
        $returnDate = $_POST['tanggal'] ?? date('Y-m-d');
        $redirectUrl = url('kelola-absen-pegawai?tab=' . urlencode($returnTab) . '&tanggal=' . urlencode($returnDate));

        try {
            $pegawaiId = (int)($_POST['pegawai_id'] ?? 0);
            $tanggal = trim((string)($_POST['tanggal'] ?? ''));
            $evaluasi = trim((string)($_POST['evaluasi'] ?? ''));
            $tindakLanjut = trim((string)($_POST['tindak_lanjut'] ?? ''));

            if (!$pegawaiId || empty($tanggal)) {
                Response::redirect($redirectUrl, 'Data pegawai atau tanggal tidak valid.', 'error');
            }

            // Periksa hak akses unit
            $effUnit = AbsenPegawaiModel::getEffectiveUserUnit();
            if ($effUnit !== null) {
                $pegUnit = AbsenPegawaiModel::getPegawaiUnit($pegawaiId);
                if ($pegUnit && strcasecmp($pegUnit, $effUnit) !== 0 && stripos($pegUnit, $effUnit) === false) {
                    Response::redirect($redirectUrl, 'Anda tidak memiliki hak akses untuk memberikan catatan pegawai unit ' . $pegUnit, 'error');
                }
            }

            $u = Auth::user();
            $evaluator = $u['nama'] ?? ($u['username'] ?? 'Admin');

            $success = AbsenPegawaiModel::saveCatatanTerlambat($pegawaiId, $tanggal, $evaluasi, $tindakLanjut, $evaluator);
            if ($success) {
                Response::redirect($redirectUrl, 'Catatan evaluasi & tindak lanjut keterlambatan berhasil disimpan.', 'success');
            } else {
                Response::redirect($redirectUrl, 'Gagal menyimpan catatan evaluasi keterlambatan.', 'error');
            }
        } catch (\Throwable $e) {
            Response::redirect($redirectUrl, 'Terjadi kesalahan: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Hapus / Reset Catatan Keterlambatan
     */
    public static function deleteCatatanTerlambat(): void
    {
        CSRF::validate();
        $returnTab = $_POST['return_tab'] ?? 'dashboard';
        $returnDate = $_POST['tanggal'] ?? date('Y-m-d');
        $redirectUrl = url('kelola-absen-pegawai?tab=' . urlencode($returnTab) . '&tanggal=' . urlencode($returnDate));

        try {
            $pegawaiId = (int)($_POST['pegawai_id'] ?? 0);
            $tanggal = trim((string)($_POST['tanggal'] ?? ''));

            if (!$pegawaiId || empty($tanggal)) {
                Response::redirect($redirectUrl, 'Data pegawai atau tanggal tidak valid.', 'error');
            }

            // Periksa hak akses unit
            $effUnit = AbsenPegawaiModel::getEffectiveUserUnit();
            if ($effUnit !== null) {
                $pegUnit = AbsenPegawaiModel::getPegawaiUnit($pegawaiId);
                if ($pegUnit && strcasecmp($pegUnit, $effUnit) !== 0 && stripos($pegUnit, $effUnit) === false) {
                    Response::redirect($redirectUrl, 'Anda tidak memiliki hak akses untuk menghapus catatan pegawai unit ' . $pegUnit, 'error');
                }
            }

            $success = AbsenPegawaiModel::deleteCatatanTerlambat($pegawaiId, $tanggal);
            if ($success) {
                Response::redirect($redirectUrl, 'Catatan evaluasi & tindak lanjut berhasil direset.', 'success');
            } else {
                Response::redirect($redirectUrl, 'Gagal mereset catatan evaluasi.', 'error');
            }
        } catch (\Throwable $e) {
            Response::redirect($redirectUrl, 'Terjadi kesalahan: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Simpan Pengaturan Hari Libur (Tanggal tunggal atau rentang tanggal, Minggu otomatis dilewati)
     */
    public static function saveLibur(): void
    {
        CSRF::validate();
        $redirectUrl = url('kelola-absen-pegawai?tab=libur');

        try {
            $tanggalMulai = trim((string)($_POST['tanggal_mulai'] ?? ''));
            $tanggalSelesai = trim((string)($_POST['tanggal_selesai'] ?? ''));
            $namaLibur = trim((string)($_POST['nama_libur'] ?? ''));
            $jenis = trim((string)($_POST['jenis'] ?? 'nasional'));
            $keterangan = trim((string)($_POST['keterangan'] ?? ''));

            $res = AbsenPegawaiModel::saveLibur(
                $tanggalMulai,
                $tanggalSelesai !== '' ? $tanggalSelesai : null,
                $namaLibur,
                $jenis,
                $keterangan !== '' ? $keterangan : null,
                Auth::id()
            );

            $msg = "Hari libur \"{$namaLibur}\" berhasil disimpan ({$res['saved']} tanggal).";
            if (!empty($res['skipped_sunday'])) {
                $msg .= " {$res['skipped_sunday']} hari Minggu otomatis dilewati karena sudah libur rutin.";
            }
            $msg .= ' Sinkron otomatis ke Absen Pegawai & Jurnal Staff.';

            Response::redirect($redirectUrl, $msg, 'success');
        } catch (Throwable $e) {
            Response::redirect($redirectUrl, $e->getMessage(), 'error');
        }
    }

    /**
     * Hapus Entri Hari Libur
     */
    public static function deleteLibur(): void
    {
        CSRF::validate();
        $redirectUrl = url('kelola-absen-pegawai?tab=libur');

        try {
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new Exception('ID hari libur tidak valid.');
            }
            AbsenPegawaiModel::deleteLibur($id);
            Response::redirect($redirectUrl, 'Hari libur berhasil dihapus. Rekap absen & jurnal staff otomatis menyesuaikan.', 'success');
        } catch (Throwable $e) {
            Response::redirect($redirectUrl, $e->getMessage(), 'error');
        }
    }

    /**
     * Simpan Nomor Urut Custom Pegawai Per Unit di Rekap Absen
     */
    public static function saveUrutanUnit(): void
    {
        CSRF::validate();
        $returnView = trim((string)($_POST['rekap_view'] ?? 'periode'));
        $unitTugas = trim((string)($_POST['unit_tugas'] ?? ''));
        $startDate = trim((string)($_POST['start_date'] ?? ''));
        $endDate = trim((string)($_POST['end_date'] ?? ''));

        $qs = [
            'tab'        => 'rekap',
            'rekap_view' => in_array($returnView, ['periode', 'harian', 'matrix'], true) ? $returnView : 'periode',
        ];
        if ($unitTugas !== '') $qs['unit_tugas'] = $unitTugas;
        if ($startDate !== '') $qs['start_date'] = $startDate;
        if ($endDate !== '') $qs['end_date'] = $endDate;
        $redirectUrl = url('kelola-absen-pegawai?' . http_build_query($qs));

        try {
            $effUnit = AbsenPegawaiModel::getEffectiveUserUnit();
            if ($effUnit !== null && strcasecmp($unitTugas, $effUnit) !== 0 && stripos($unitTugas, $effUnit) === false) {
                throw new Exception('Anda hanya memiliki akses untuk mengatur urutan unit ' . $effUnit . '.');
            }

            $urutanRaw = $_POST['urutan'] ?? [];
            if (!is_array($urutanRaw)) {
                $urutanRaw = [];
            }

            AbsenPegawaiModel::saveUrutanUnit($unitTugas, $urutanRaw);
            Response::redirect($redirectUrl, 'Nomor urut custom untuk unit ' . $unitTugas . ' berhasil disimpan.', 'success');
        } catch (Throwable $e) {
            Response::redirect($redirectUrl, 'Gagal menyimpan urutan unit: ' . $e->getMessage(), 'error');
        }
    }
}
