<?php
/**
 * Migration & Synchronization Script for Siswa Table
 */

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/database.php';

try {
    $pdo = new PDO(
        sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', DB_HOST, DB_PORT, DB_NAME, DB_CHARSET),
        DB_USER,
        DB_PASS,
        DB_OPTIONS
    );
    echo "Connected to " . DB_NAME . "\n";

    // Drop unique key uk_siswa_nis if exists
    try {
        $pdo->exec("ALTER TABLE `siswa` DROP INDEX `uk_siswa_nis`");
        echo "Dropped uk_siswa_nis unique key\n";
    } catch (Exception $e) {}

    // Add unique key uk_siswa_id_siswa if not exists
    try {
        $pdo->exec("ALTER TABLE `siswa` ADD UNIQUE KEY `uk_siswa_id_siswa` (`id_siswa`)");
    } catch (Exception $e) {}

    // Sync all 1059 records from jurnal.tb_data_siswa
    $pdoJurnal = new PDO('mysql:host=localhost;dbname=jurnal;charset=utf8mb4', 'root', '');
    $jurnalRows = $pdoJurnal->query("SELECT * FROM tb_data_siswa")->fetchAll(PDO::FETCH_ASSOC);

    $pdo->exec("TRUNCATE TABLE `siswa`");

    $insertStmt = $pdo->prepare("
        INSERT INTO `siswa` (
            id_siswa, nis, nisn, nama_lengkap, nama, tempat_lahir, tgl_lahir, tanggal_lahir, umur,
            jenis_kelamin, no_nik, no_kk, no_registrasi_akta, kebutuhan_khusus, anak_ke, alergi, nama_alergi,
            tinggi_badan, berat_badan, asal_sekolah, alamat_sekolah, jenjang, kelas, tahun_ajaran, semester, dapodik,
            alamat, rt, rw, dusun, kelurahan, kecamatan, kota, provinsi, kode_pos, lintang, bujur,
            tempat_tinggal, moda_transportasi, no_hp, telepon, email,
            nama_ayah, nik_ayah, tahun_lahir_ayah, pendidikan_ayah, pekerjaan_ayah, kantor_ayah, jabatan_ayah, penghasilan_ayah, kebutuhan_khusus_ayah,
            nama_ibu, nik_ibu, tahun_lahir_ibu, pendidikan_ibu, pekerjaan_ibu, kantor_ibu, jabatan_ibu, penghasilan_ibu, kebutuhan_khusus_ibu,
            is_active
        ) VALUES (
            :id_siswa, :nis, :nisn, :nama_lengkap, :nama, :tempat_lahir, :tgl_lahir, :tanggal_lahir, :umur,
            :jenis_kelamin, :no_nik, :no_kk, :no_registrasi_akta, :kebutuhan_khusus, :anak_ke, :alergi, :nama_alergi,
            :tinggi_badan, :berat_badan, :asal_sekolah, :alamat_sekolah, :jenjang, :kelas, :tahun_ajaran, :semester, :dapodik,
            :alamat, :rt, :rw, :dusun, :kelurahan, :kecamatan, :kota, :provinsi, :kode_pos, :lintang, :bujur,
            :tempat_tinggal, :moda_transportasi, :no_hp, :telepon, :email,
            :nama_ayah, :nik_ayah, :tahun_lahir_ayah, :pendidikan_ayah, :pekerjaan_ayah, :kantor_ayah, :jabatan_ayah, :penghasilan_ayah, :kebutuhan_khusus_ayah,
            :nama_ibu, :nik_ibu, :tahun_lahir_ibu, :pendidikan_ibu, :pekerjaan_ibu, :kantor_ibu, :jabatan_ibu, :penghasilan_ibu, :kebutuhan_khusus_ibu,
            1
        )
    ");

    $count = 0;
    foreach ($jurnalRows as $r) {
        $jk = ($r['jenis_kelamin'] === 'Perempuan' || $r['jenis_kelamin'] === 'P') ? 'P' : 'L';
        $tgl = !empty($r['tgl_lahir']) ? $r['tgl_lahir'] : null;

        $insertStmt->execute([
            'id_siswa' => $r['id_siswa'] ?? ('SISWA-' . sprintf('%06d', $count + 1)),
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

    echo "Successfully imported all {$count} students into db_portal_bip.siswa!\n";

} catch (Exception $e) {
    echo "Migration Error: " . $e->getMessage() . "\n";
}
