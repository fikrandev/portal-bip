<?php
/**
 * Dashboard Controller
 * Main portal page showing accessible module cards and role-based dashboard feature widgets
 */

class DashboardController
{
    /**
     * Show dashboard with module cards and accessible dashboard widgets
     */
    public static function index(): void
    {
        $pageTitle = 'Dashboard';
        $pageDescription = 'Portal utama ' . APP_NAME;
        
        // Get accessible modules (skip "Dashboard" itself from cards)
        $allModules = RBAC::getAccessibleModules();
        $modules = array_filter($allModules, fn($m) => $m['slug'] !== 'dashboard');
        $modules = array_values($modules); // re-index
        
        $db = Database::getInstance();
        $today = date('Y-m-d');
        
        // Dynamic dashboard features data container
        $dashData = [];

        // 1. Kesiswaan
        if (RBAC::canAccessDashboardFeature('siswa.dashboard')) {
            try {
                $totalSiswa = $db->count('siswa');
                $siswaL = $db->count('siswa', 'LOWER(jenis_kelamin) IN ("l", "laki-laki")');
                $siswaP = $db->count('siswa', 'LOWER(jenis_kelamin) IN ("p", "perempuan")');
                $totalKelas = $db->count('kelas');
                $dashData['siswa'] = [
                    'total' => $totalSiswa,
                    'laki' => $siswaL,
                    'perempuan' => $siswaP,
                    'kelas' => $totalKelas,
                ];
            } catch (Throwable $e) {}
        }

        // 2. Kepegawaian
        if (RBAC::canAccessDashboardFeature('pegawai.dashboard')) {
            try {
                $totalPegawai = $db->count('pegawai');
                $guruCount = $db->count('pegawai', 'nama LIKE "%S.Pd%" OR nama LIKE "%Gr%" OR nama LIKE "%Lc%" OR nama LIKE "%Ust%" OR id IN (SELECT DISTINCT guru_id FROM jadwal_pelajaran WHERE guru_id IS NOT NULL)');
                $dashData['pegawai'] = [
                    'total' => $totalPegawai,
                    'guru' => $guruCount > 0 ? $guruCount : $totalPegawai,
                    'staf' => max(0, $totalPegawai - $guruCount),
                ];
            } catch (Throwable $e) {}
        }

        // 3. Presensi Siswa
        if (RBAC::canAccessDashboardFeature('absen_siswa.dashboard')) {
            try {
                $presensiHariIni = $db->find("
                    SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN LOWER(status) = 'h' OR LOWER(status) = 'hadir' THEN 1 ELSE 0 END) as hadir,
                        SUM(CASE WHEN LOWER(status) = 's' OR LOWER(status) = 'sakit' THEN 1 ELSE 0 END) as sakit,
                        SUM(CASE WHEN LOWER(status) = 'i' OR LOWER(status) = 'izin' THEN 1 ELSE 0 END) as izin,
                        SUM(CASE WHEN LOWER(status) = 'a' OR LOWER(status) = 'alpha' THEN 1 ELSE 0 END) as alpha
                    FROM presensi_kelas_detail
                    WHERE DATE(created_at) = ?
                ", [$today]);

                $dashData['absen_siswa'] = [
                    'total' => (int)($presensiHariIni['total'] ?? 0),
                    'hadir' => (int)($presensiHariIni['hadir'] ?? 0),
                    'sakit' => (int)($presensiHariIni['sakit'] ?? 0),
                    'izin' => (int)($presensiHariIni['izin'] ?? 0),
                    'alpha' => (int)($presensiHariIni['alpha'] ?? 0),
                ];
            } catch (Throwable $e) {}
        }

        // 4. Presensi Pegawai
        if (RBAC::canAccessDashboardFeature('absen_pegawai.dashboard')) {
            try {
                $absenPegHariIni = $db->find("
                    SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN jam_masuk IS NOT NULL AND jam_masuk != '' THEN 1 ELSE 0 END) as hadir,
                        SUM(CASE WHEN jam_pulang IS NOT NULL AND jam_pulang != '' THEN 1 ELSE 0 END) as pulang
                    FROM absensi_pegawai
                    WHERE tanggal = ?
                ", [$today]);

                $dashData['absen_pegawai'] = [
                    'hadir' => (int)($absenPegHariIni['hadir'] ?? 0),
                    'pulang' => (int)($absenPegHariIni['pulang'] ?? 0),
                ];
            } catch (Throwable $e) {}
        }

        // 5. Jurnal Mengajar Guru
        if (RBAC::canAccessDashboardFeature('jurnal.dashboard')) {
            try {
                $jurnalHariIni = $db->find("
                    SELECT 
                        COUNT(*) as total_jurnal,
                        COALESCE(SUM(jumlah_jp), 0) as total_jp,
                        COUNT(DISTINCT guru_id) as total_guru
                    FROM jurnal_mengajar
                    WHERE tanggal = ?
                ", [$today]);

                $dashData['jurnal'] = [
                    'total' => (int)($jurnalHariIni['total_jurnal'] ?? 0),
                    'total_jp' => (int)($jurnalHariIni['total_jp'] ?? 0),
                    'total_guru' => (int)($jurnalHariIni['total_guru'] ?? 0),
                ];
            } catch (Throwable $e) {}
        }

        // 6. Mutaba'ah Ibadah Guru
        if (RBAC::canAccessDashboardFeature('ibadah_guru.dashboard')) {
            try {
                $ibadahCount = $db->count('mutabaah_ibadah_guru', 'tanggal = ?', [$today]);
                $dashData['ibadah_guru'] = [
                    'total_terisi' => $ibadahCount,
                ];
            } catch (Throwable $e) {}
        }

        // 7. SPMB (Penerimaan Santri Baru)
        if (RBAC::canAccessDashboardFeature('spmb.dashboard')) {
            try {
                $totalSpmb = $db->count('spmb_pendaftar');
                $menungguVerif = $db->count('spmb_pendaftar', 'status_verifikasi = "menunggu" OR status_verifikasi IS NULL OR status_verifikasi = ""');
                $lulusSpmb = $db->count('spmb_pendaftar', 'status_kelulusan = "lulus"');
                $dashData['spmb'] = [
                    'total' => $totalSpmb,
                    'menunggu' => $menungguVerif,
                    'lulus' => $lulusSpmb,
                ];
            } catch (Throwable $e) {}
        }

        // 8. Cuti Pegawai
        if (RBAC::canAccessDashboardFeature('cuti.dashboard')) {
            try {
                $cutiPending = $db->count('cuti_pengajuan', 'status = "menunggu" OR status IS NULL');
                $cutiApproved = $db->count('cuti_pengajuan', 'status = "disetujui"');
                $dashData['cuti'] = [
                    'pending' => $cutiPending,
                    'approved' => $cutiApproved,
                ];
            } catch (Throwable $e) {}
        }

        // 9. Izin Guru
        if (RBAC::canAccessDashboardFeature('izin_guru.dashboard')) {
            try {
                $izinPending = $db->count('izin_guru', 'status = "menunggu" OR status IS NULL');
                $izinHariIni = $db->count('izin_guru', 'tanggal_mulai <= ? AND tanggal_selesai >= ?', [$today, $today]);
                $dashData['izin_guru'] = [
                    'pending' => $izinPending,
                    'hari_ini' => $izinHariIni,
                ];
            } catch (Throwable $e) {}
        }

        // 10. Sarpras
        if (RBAC::canAccessDashboardFeature('sarpras.dashboard')) {
            try {
                $totalBarang = $db->count('sarpras_barang');
                $pinjamAktif = $db->count('sarpras_peminjaman', 'tanggal_kembali IS NULL OR tanggal_kembali = ""');
                $dashData['sarpras'] = [
                    'total_barang' => $totalBarang,
                    'pinjam_aktif' => $pinjamAktif,
                ];
            } catch (Throwable $e) {}
        }

        // 11. Pelanggaran Siswa
        if (RBAC::canAccessDashboardFeature('pelanggaran_siswa.dashboard')) {
            try {
                $terlambatHariIni = $db->count('keterlambatan_siswa', 'tanggal = ?', [$today]);
                $totalTerlambat = $db->count('keterlambatan_siswa');
                $dashData['pelanggaran'] = [
                    'hari_ini' => $terlambatHariIni,
                    'total' => $totalTerlambat,
                ];
            } catch (Throwable $e) {}
        }

        // 12. Sistem & Pengguna
        $canSeeSystemStats = Auth::isSuperAdmin() || RBAC::canAccessDashboardFeature('users.dashboard') || RBAC::canAccessDashboardFeature('roles.dashboard') || RBAC::canAccessDashboardFeature('modules.dashboard');
        if ($canSeeSystemStats) {
            try {
                $totalUsers = $db->count('users', 'is_active = 1');
                $totalModules = $db->count('modules', 'is_active = 1');
                $totalRoles = $db->count('roles');
                $dashData['system'] = [
                    'total_users' => $totalUsers,
                    'total_modules' => $totalModules,
                    'total_roles' => $totalRoles,
                ];
            } catch (Throwable $e) {}
        }

        // Extra JS for dashboard
        $extraJs = '<script src="' . asset('js/dashboard.js') . '"></script>';
        
        $hideSidebar = true;
        
        ob_start();
        include MODULES_PATH . '/dashboard/views/index.php';
        $content = ob_get_clean();
        
        include TEMPLATES_PATH . '/layouts/app.php';
    }
}
