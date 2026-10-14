<?php
/**
 * View / Template: Formulir Peserta Didik Standar Dapodik Kemdikbud
 * Yayasan Bina Insan Palu (Portal BIP)
 * 
 * Sesuai format resmi Dapodik/EMIS untuk SEMUA UNIT (PAUD/TK, SD, SMP, SMA).
 * Menampilkan data pendaftar / siswa langsung tanpa kotak huruf per karakter
 * ("biarkan saja langsung").
 */

if (!function_exists('url')) {
    function url(string $path = ''): string {
        $base = defined('APP_URL') ? APP_URL : '';
        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }
}
if (!function_exists('e')) {
    function e($val): string {
        return htmlspecialchars((string)($val ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('renderDapodikFormulirPesertaDidik')) {
    function renderDapodikFormulirPesertaDidik(array $data, array $options = []): void {
        // Normalisasi data pendaftar vs siswa
        $jenjangRaw = strtolower(trim((string)($data['jenjang'] ?? 'sd')));
        if (str_contains($jenjangRaw, 'paud') || str_contains($jenjangRaw, 'tk')) {
            $unitKey = 'paud';
        } elseif (str_contains($jenjangRaw, 'smp')) {
            $unitKey = 'smp';
        } elseif (str_contains($jenjangRaw, 'sma')) {
            $unitKey = 'sma';
        } else {
            $unitKey = 'sd';
        }

        // Unit Meta Settings
        $unitConfigs = [
            'paud' => [
                'nama'   => 'PAUD ISLAM TERPADU BINA INSAN PALU',
                'sub'    => 'TK IT BINA INSAN PALU',
                'npsn'   => '70037291',
                'najsit' => '7060102003',
                'logo'   => url('uploads/settings/logo_paud_1788072952.png'),
            ],
            'sd' => [
                'nama'   => 'SD ISLAM TERPADU BINA INSAN PALU',
                'sub'    => 'SD IT BINA INSAN PALU',
                'npsn'   => '69979223',
                'najsit' => '7060102003',
                'logo'   => url('uploads/settings/logo_sd_1788072952.png'),
            ],
            'smp' => [
                'nama'   => 'SMP ISLAM TERPADU BINA INSAN PALU',
                'sub'    => 'SMP IT BINA INSAN PALU',
                'npsn'   => '70031371',
                'najsit' => '7060102003',
                'logo'   => url('uploads/settings/logo_smp_1788072952.png'),
            ],
            'sma' => [
                'nama'   => 'SMA ISLAM TERPADU BINA INSAN PALU',
                'sub'    => 'SMA IT BINA INSAN PALU',
                'npsn'   => '70058217',
                'najsit' => '7060102003',
                'logo'   => url('uploads/settings/logo_sma_1788072952.png'),
            ],
        ];

        $unit = $unitConfigs[$unitKey] ?? $unitConfigs['sd'];

        // Helper date splitting
        $tglDaftar = $data['tanggal_daftar'] ?? ($data['created_at'] ?? date('Y-m-d'));
        $timeDaftar = strtotime($tglDaftar) ?: time();
        $tglFormulirD = date('d', $timeDaftar);
        $tglFormulirM = date('m', $timeDaftar);
        $tglFormulirY = date('Y', $timeDaftar);

        // Nomor Formulir / Pendaftaran / NIS
        $noFormulir = $data['nomor_pendaftaran'] ?? ($data['nis'] ?? ($data['nisn'] ?? '-'));

        // Identitas Siswa
        $namaLengkap = strtoupper(trim((string)($data['nama_lengkap'] ?? ($data['nama'] ?? '-'))));
        $jkRaw = strtoupper(trim((string)($data['jenis_kelamin'] ?? 'L')));
        $isLaki = ($jkRaw === 'L' || $jkRaw === 'LAKI-LAKI' || str_starts_with($jkRaw, 'L'));
        $nisn = $data['nisn'] ?? '-';
        $nik = $data['nik'] ?? ($data['no_nik'] ?? '-');
        $noKk = $data['no_kk'] ?? '-';
        $tempatLahir = $data['tempat_lahir'] ?? '-';
        
        $tglLahirRaw = $data['tanggal_lahir'] ?? ($data['tgl_lahir'] ?? '');
        $timeLahir = !empty($tglLahirRaw) ? strtotime($tglLahirRaw) : null;
        $tglLahirD = $timeLahir ? date('d', $timeLahir) : '-';
        $tglLahirM = $timeLahir ? date('m', $timeLahir) : '-';
        $tglLahirY = $timeLahir ? date('Y', $timeLahir) : '-';

        $noAkta = $data['no_akta_lahir'] ?? ($data['no_registrasi_akta'] ?? '-');
        $agama = strtolower(trim((string)($data['agama'] ?? 'islam')));

        // Kode Agama Dapodik
        $kodeAgama = '01';
        if (str_contains($agama, 'kristen') || str_contains($agama, 'protestan')) $kodeAgama = '02';
        elseif (str_contains($agama, 'katolik') || str_contains($agama, 'katholik')) $kodeAgama = '03';
        elseif (str_contains($agama, 'hindu')) $kodeAgama = '04';
        elseif (str_contains($agama, 'budha') || str_contains($agama, 'buddha')) $kodeAgama = '05';
        elseif (str_contains($agama, 'konghucu')) $kodeAgama = '06';
        elseif (str_contains($agama, 'kepercayaan')) $kodeAgama = '07';

        // Alamat
        $alamat = $data['alamat'] ?? '-';
        $rt = $data['rt'] ?? '-';
        $rw = $data['rw'] ?? '-';
        $dusun = $data['dusun'] ?? '-';
        $kelurahan = $data['kelurahan'] ?? '-';
        $kecamatan = $data['kecamatan'] ?? '-';
        $kodePos = $data['kode_pos'] ?? '-';
        $lintang = $data['lintang'] ?? '-';
        $bujur = $data['bujur'] ?? '-';

        // Tempat Tinggal Kode
        $tempatTinggal = strtolower(trim((string)($data['tempat_tinggal'] ?? 'bersama orang tua')));
        $kodeTinggal = '01';
        if (str_contains($tempatTinggal, 'wali')) $kodeTinggal = '02';
        elseif (str_contains($tempatTinggal, 'kos') || str_contains($tempatTinggal, 'kost')) $kodeTinggal = '03';
        elseif (str_contains($tempatTinggal, 'asrama') || str_contains($tempatTinggal, 'pondok')) $kodeTinggal = '04';
        elseif (str_contains($tempatTinggal, 'panti')) $kodeTinggal = '05';

        // Moda Transportasi Kode
        $transport = strtolower(trim((string)($data['moda_transportasi'] ?? '')));
        $kodeTransport = '02'; // default kendaraan pribadi
        if (str_contains($transport, 'kaki')) $kodeTransport = '01';
        elseif (str_contains($transport, 'umum') || str_contains($transport, 'angkot') || str_contains($transport, 'pete')) $kodeTransport = '03';
        elseif (str_contains($transport, 'jemputan') || str_contains($transport, 'antar')) $kodeTransport = '04';
        elseif (str_contains($transport, 'kereta')) $kodeTransport = '05';
        elseif (str_contains($transport, 'ojek')) $kodeTransport = '06';
        elseif (str_contains($transport, 'delman') || str_contains($transport, 'becak')) $kodeTransport = '07';
        elseif (str_contains($transport, 'perahu') || str_contains($transport, 'rakit')) $kodeTransport = '08';
        elseif (!empty($transport) && !str_contains($transport, 'pribadi') && !str_contains($transport, 'motor') && !str_contains($transport, 'mobil')) $kodeTransport = '09';

        $anakKe = $data['anak_ke'] ?? '1';
        $hasAlergi = (!empty($data['alergi']) && strtolower($data['alergi']) !== 'tidak' && strtolower($data['alergi']) !== 'tidak ada');
        $namaAlergi = $data['nama_alergi'] ?? ($data['alergi'] ?? '-');

        // Helper Kode Pendidikan
        $fnPendidikan = function($val) {
            $v = strtolower(trim((string)$val));
            if (empty($v) || $v === '-' || str_contains($v, 'tidak')) return '01';
            if (str_contains($v, 'putus')) return '02';
            if (str_contains($v, 'sd')) return '03';
            if (str_contains($v, 'smp') || str_contains($v, 'mts')) return '04';
            if (str_contains($v, 'sma') || str_contains($v, 'smk') || str_contains($v, 'ma') || str_contains($v, 'sederajat')) return '05';
            if (str_contains($v, 'd1')) return '06';
            if (str_contains($v, 'd2')) return '07';
            if (str_contains($v, 'd3')) return '08';
            if (str_contains($v, 'd4') || str_contains($v, 's1') || str_contains($v, 'sarjana')) return '09';
            if (str_contains($v, 's2') || str_contains($v, 'magister')) return '10';
            if (str_contains($v, 's3') || str_contains($v, 'doktor')) return '11';
            return '05';
        };

        // Helper Kode Pekerjaan
        $fnPekerjaan = function($val) {
            $v = strtolower(trim((string)$val));
            if (empty($v) || $v === '-' || str_contains($v, 'tidak bekerja') || str_contains($v, 'irt') || str_contains($v, 'rumah tangga')) return '01';
            if (str_contains($v, 'nelayan')) return '02';
            if (str_contains($v, 'petani')) return '03';
            if (str_contains($v, 'peternak')) return '04';
            if (str_contains($v, 'pns') || str_contains($v, 'tni') || str_contains($v, 'polri') || str_contains($v, 'asn')) return '05';
            if (str_contains($v, 'karyawan') || str_contains($v, 'swasta') || str_contains($v, 'bumn') || str_contains($v, 'guru') || str_contains($v, 'dosen') || str_contains($v, 'dokter')) return '06';
            if (str_contains($v, 'pedagang kecil')) return '07';
            if (str_contains($v, 'pedagang besar')) return '08';
            if (str_contains($v, 'wiraswasta') || str_contains($v, 'usaha') || str_contains($v, 'bisnis')) return '09';
            if (str_contains($v, 'wirausaha')) return '10';
            if (str_contains($v, 'buruh')) return '11';
            if (str_contains($v, 'pensiun')) return '12';
            return '06';
        };

        // Helper Kode Penghasilan
        $fnPenghasilan = function($val) {
            $v = strtolower(trim((string)$val));
            if (empty($v) || $v === '-' || str_contains($v, 'tidak berpenghasilan') || str_contains($v, '0')) return '07';
            if (str_contains($v, 'kurang') || str_contains($v, '< 500') || str_contains($v, '< 1')) return '01';
            if (str_contains($v, '500') && str_contains($v, '999')) return '02';
            if (str_contains($v, '1.000.000') && str_contains($v, '1.999')) return '03';
            if (str_contains($v, '1.000.000') && str_contains($v, '3.000')) return '03';
            if (str_contains($v, '2.000.000') || str_contains($v, '3.000.000') || str_contains($v, '4.999')) return '04';
            if (str_contains($v, '5.000.000') || str_contains($v, '10.000')) return '05';
            if (str_contains($v, 'lebih dari') || str_contains($v, '> 20') || str_contains($v, '> 10')) return '06';
            return '04';
        };

        // Ayah
        $namaAyah = $data['nama_ayah'] ?? '-';
        $nikAyah = $data['nik_ayah'] ?? '-';
        $thnAyah = $data['tahun_lahir_ayah'] ?? '-';
        $pendAyah = $data['pendidikan_ayah'] ?? '-';
        $kodePendAyah = $fnPendidikan($pendAyah);
        $pekAyah = $data['pekerjaan_ayah'] ?? '-';
        $kodePekAyah = $fnPekerjaan($pekAyah);
        $pengAyah = $data['penghasilan_ayah'] ?? '-';
        $kodePengAyah = $fnPenghasilan($pengAyah);

        // Ibu
        $namaIbu = $data['nama_ibu'] ?? '-';
        $nikIbu = $data['nik_ibu'] ?? '-';
        $thnIbu = $data['tahun_lahir_ibu'] ?? '-';
        $pendIbu = $data['pendidikan_ibu'] ?? '-';
        $kodePendIbu = $fnPendidikan($pendIbu);
        $pekIbu = $data['pekerjaan_ibu'] ?? '-';
        $kodePekIbu = $fnPekerjaan($pekIbu);
        $pengIbu = $data['penghasilan_ibu'] ?? '-';
        $kodePengIbu = $fnPenghasilan($pengIbu);

        // Wali
        $namaWali = $data['nama_wali'] ?? '-';
        $nikWali = $data['nik_wali'] ?? '-';
        $thnWali = $data['tahun_lahir_wali'] ?? '-';
        $pendWali = $data['pendidikan_wali'] ?? '-';
        $kodePendWali = $fnPendidikan($pendWali);
        $pekWali = $data['pekerjaan_wali'] ?? '-';
        $kodePekWali = $fnPekerjaan($pekWali);
        $pengWali = $data['penghasilan_wali'] ?? '-';
        $kodePengWali = $fnPenghasilan($pengWali);

        // Kontak
        $telpRumah = $data['telepon'] ?? '-';
        $noHp = $data['no_hp'] ?? ($data['no_wa'] ?? ($data['no_hp_ayah'] ?? ($data['no_hp_ibu'] ?? '-')));
        $email = $data['email'] ?? '-';

        // Periodik
        $tinggi = $data['tinggi_badan'] ?? '-';
        $berat = $data['berat_badan'] ?? '-';
        $lingkarKepala = $data['lingkar_kepala'] ?? '-';
        $jumlahSaudara = $data['jumlah_saudara'] ?? '0';

        // Pendaftaran / Rincian Sekolah
        $jenisDaftar = strtolower(trim((string)($data['jenis_pendaftaran'] ?? 'siswa baru')));
        $kodeJenisDaftar = '01';
        if (str_contains($jenisDaftar, 'pindah')) $kodeJenisDaftar = '02';
        elseif (str_contains($jenisDaftar, 'kembali')) $kodeJenisDaftar = '03';

        $sekolahAsal = $data['asal_sekolah'] ?? '-';
        $nis = $data['nis'] ?? ($data['nomor_pendaftaran'] ?? '-');
        ?>

        <style>
            @page {
                size: 215mm 330mm; /* Kertas F4 / Folio Resmi (21.5cm x 33cm) */
                margin: 10mm;      /* Margin 1cm keliling (kiri, kanan, bawah, atas) */
            }

            /* ── DAPODIK OFFICIAL FORM STYLES ── */
            .dapodik-form-wrapper {
                font-family: Arial, Helvetica, sans-serif;
                color: #000000;
                font-size: 7.2pt;
                line-height: 1.15;
                background: #ffffff;
                width: 100%;
                box-sizing: border-box;
            }

            .dapodik-page {
                position: relative;
                width: 215mm;
                max-width: 100%;
                min-height: 310mm;
                padding: 10mm;
                margin: 0 auto 20px auto;
                background: #ffffff;
                box-sizing: border-box;
                border: 1px solid #cbd5e1;
                box-shadow: 0 4px 16px rgba(0,0,0,0.06);
            }

            @media print {
                @page {
                    size: 215mm 330mm; /* Standar Kertas F4 / Folio */
                    margin: 10mm;      /* Margin 1cm (kiri, kanan, bawah, atas) */
                }
                body {
                    margin: 0 !important;
                    padding: 0 !important;
                    background: #ffffff !important;
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }
                .no-print {
                    display: none !important;
                }
                .dapodik-form-wrapper {
                    background: transparent !important;
                    padding: 0 !important;
                    margin: 0 !important;
                    width: 100% !important;
                }
                .dapodik-page {
                    border: none !important;
                    box-shadow: none !important;
                    margin: 0 !important;
                    padding: 0 !important; /* Margin 10mm sudah ditangani @page */
                    width: 100% !important;
                    max-width: 195mm !important; /* 215mm - 20mm */
                    min-height: 0 !important;
                    height: auto !important;
                    max-height: 310mm !important;
                    page-break-after: always !important;
                    break-after: page !important;
                    page-break-inside: avoid !important;
                    break-inside: avoid !important;
                    box-sizing: border-box !important;
                    overflow: hidden !important; /* Mencegah lembar ke-2 pecah menjadi 2 */
                }
                .dapodik-page:last-child {
                    page-break-after: avoid !important;
                    break-after: avoid !important;
                }
            }

            /* Header Kop Unit */
            .dp-header-kop {
                display: flex;
                align-items: flex-start;
                justify-content: flex-end;
                margin-bottom: 2px;
                gap: 10px;
            }
            .dp-header-logo {
                width: 44px;
                height: 44px;
                object-fit: contain;
            }
            .dp-header-info {
                text-align: right;
                font-family: Arial, Helvetica, sans-serif;
            }
            .dp-header-title {
                font-size: 8.8pt;
                font-weight: bold;
                color: #0f172a;
                line-height: 1.15;
            }
            .dp-header-meta {
                font-size: 7.2pt;
                font-weight: bold;
                color: #1e293b;
                margin-top: 1px;
                line-height: 1.15;
            }

            /* Banner Bars */
            .dp-title-bar {
                background: #4ba3e3;
                color: #ffffff;
                text-align: center;
                font-size: 11pt;
                font-weight: bold;
                letter-spacing: 1px;
                padding: 2.5px 0;
                margin: 2px 0 3px 0;
                border-radius: 2px;
            }

            .dp-sub-bar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                font-size: 7.5pt;
                margin-bottom: 3px;
                padding: 0 2px;
            }
            .dp-sub-box {
                display: inline-block;
                border: 1px solid #000;
                padding: 0.5px 4px;
                font-weight: bold;
                font-family: monospace;
            }

            .dp-section-bar {
                background: #001fd6;
                color: #ffffff;
                font-weight: bold;
                font-size: 8pt;
                letter-spacing: 0.5px;
                padding: 1.5px 6px;
                margin: 3px 0 2px 0;
                border-radius: 1px;
            }

            .dp-sub-section-bar {
                background: #5ea3eb;
                color: #ffffff;
                font-weight: bold;
                font-size: 8pt;
                letter-spacing: 0.5px;
                text-align: center;
                padding: 2px 0;
                margin: 4px 0 2px 0;
                border-radius: 1px;
            }

            /* Form Data Rows */
            .dp-table {
                width: 100%;
                border-collapse: collapse;
            }
            .dp-table tr {
                vertical-align: top;
            }
            .dp-table td {
                padding: 0.5px 1px;
            }
            .dp-td-num {
                width: 20px;
                font-weight: normal;
                white-space: nowrap;
            }
            .dp-td-label {
                width: 190px;
                font-weight: normal;
                padding-right: 4px;
            }
            .dp-td-colon {
                width: 10px;
                text-align: center;
            }
            .dp-td-val {
                font-weight: normal;
            }

            .dp-val-direct {
                font-weight: bold;
                color: #000000;
                border-bottom: 1px dotted #94a3b8;
                padding: 0 3px;
                display: inline-block;
                min-width: 60px;
                font-size: 7.2pt;
            }
            .dp-val-direct.full {
                width: 98%;
            }

            .dp-code-box {
                display: inline-block;
                border: 1px solid #000000;
                padding: 0.5px 3.5px;
                font-weight: bold;
                font-family: monospace;
                font-size: 7.2pt;
                margin-right: 5px;
                background: #ffffff;
                min-width: 18px;
                text-align: center;
            }

            .dp-radio {
                display: inline-flex;
                align-items: center;
                gap: 3px;
                margin-right: 12px;
                font-size: 7.2pt;
            }
            .dp-radio-circle {
                display: inline-block;
                width: 9px;
                height: 9px;
                border: 1px solid #000000;
                border-radius: 50%;
                text-align: center;
                line-height: 7px;
                font-size: 6.5pt;
                vertical-align: middle;
            }
            .dp-radio-circle.checked {
                background: #000000;
            }

            .dp-desc {
                font-size: 5.6pt;
                color: #0284c7;
                font-style: italic;
                line-height: 1.05;
                margin-top: 0.3px;
                margin-bottom: 0.5px;
            }
            .dp-desc-muted {
                font-size: 5.6pt;
                color: #64748b;
                font-style: italic;
                line-height: 1.05;
            }

            .dp-opt-active {
                font-weight: bold;
                color: #000000;
                text-decoration: underline;
            }
            .dp-opt-muted {
                color: #475569;
            }

            .dp-footer-page {
                position: absolute;
                bottom: 1mm;
                right: 3mm;
                font-size: 7pt;
                color: #000000;
                font-weight: bold;
            }

            .dp-mini-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 1px;
                font-size: 7pt;
            }
            .dp-mini-table th, .dp-mini-table td {
                border: 1px solid #000000;
                padding: 1.5px 3px;
                text-align: left;
            }
            /* ── HALAMAN 1 (DATA PRIBADI) FULL-HEIGHT PER SPEC ── */
            #dapodik-hal-1 {
                display: flex;
                flex-direction: column;
            }
            #dapodik-hal-1 .dp-table {
                flex: 1 1 auto;
                width: 100%;
            }
            #dapodik-hal-1 .dp-table td {
                padding: 1.6px 1px !important;
            }
            #dapodik-hal-1 .dp-desc {
                font-size: 6.2pt !important;
                line-height: 1.15 !important;
                margin-top: 0.8px !important;
                margin-bottom: 1.2px !important;
            }
            #dapodik-hal-1 .dp-td-num,
            #dapodik-hal-1 .dp-td-label,
            #dapodik-hal-1 .dp-td-val,
            #dapodik-hal-1 .dp-val-direct,
            #dapodik-hal-1 .dp-radio,
            #dapodik-hal-1 .dp-code-box {
                font-size: 7.8pt !important;
            }
            #dapodik-hal-1 .dp-dotted-divider {
                border-bottom: 1px dotted #333333;
                margin: 3px 0;
                width: 100%;
            }
            #dapodik-hal-1 .dp-title-bar {
                padding: 3.2px 0 !important;
                margin: 3px 0 5px 0 !important;
                font-size: 12pt !important;
            }
            #dapodik-hal-1 .dp-sub-bar {
                font-size: 8pt !important;
                margin-bottom: 5px !important;
            }
            #dapodik-hal-1 .dp-section-bar {
                padding: 2.2px 6px !important;
                margin: 4px 0 3px 0 !important;
                font-size: 8.8pt !important;
            }
            #dapodik-hal-1 .dp-footer-page {
                position: relative !important;
                bottom: auto !important;
                right: auto !important;
                text-align: right;
                margin-top: 3px;
                font-size: 7.5pt;
                font-weight: bold;
            }

            /* ── HALAMAN 2 (DATA ORANG TUA KANDUNG & WALI) FULL-HEIGHT PER SPEC ── */
            #dapodik-hal-2 {
                display: flex;
                flex-direction: column;
            }
            #dapodik-hal-2 .dp-table {
                flex: 1 1 auto;
                width: 100%;
            }
            #dapodik-hal-2 .dp-table td {
                padding: 1.05px 1px !important;
            }
            #dapodik-hal-2 .dp-desc {
                font-size: 5.6pt !important;
                line-height: 1.08 !important;
                margin-top: 0.3px !important;
                margin-bottom: 0.5px !important;
                color: #0284c7 !important;
                font-style: italic !important;
            }
            #dapodik-hal-2 .dp-td-num,
            #dapodik-hal-2 .dp-td-label,
            #dapodik-hal-2 .dp-td-val,
            #dapodik-hal-2 .dp-val-direct,
            #dapodik-hal-2 .dp-radio,
            #dapodik-hal-2 .dp-code-box {
                font-size: 7.3pt !important;
            }
            #dapodik-hal-2 .dp-section-bar {
                padding: 2.2px 6px !important;
                margin: 3px 0 2px 0 !important;
                font-size: 8.5pt !important;
                background: #0000ff !important;
                color: #ffffff !important;
                font-weight: bold !important;
                line-height: 1.15;
            }
            #dapodik-hal-2 .dp-footer-page {
                position: relative !important;
                bottom: auto !important;
                right: auto !important;
                text-align: right;
                margin-top: 2px;
                font-size: 7.5pt;
                font-weight: bold;
            }

            /* ── HALAMAN 3 (KONTAK, PERIODIK, & REGISTRASI) ── */
            #dapodik-hal-3 {
                position: relative !important;
                min-height: 310mm !important;
                box-sizing: border-box !important;
            }
            #dapodik-hal-3 .dp-table {
                width: 100% !important;
                border-collapse: collapse !important;
            }
            #dapodik-hal-3 .dp-table td {
                padding: 2.2px 1px !important;
            }
            #dapodik-hal-3 .dp-desc {
                font-size: 5.6pt !important;
                line-height: 1.08 !important;
                margin-top: 0.3px !important;
                margin-bottom: 0.5px !important;
                color: #0284c7 !important;
                font-style: italic !important;
            }
            #dapodik-hal-3 .dp-td-num,
            #dapodik-hal-3 .dp-td-label,
            #dapodik-hal-3 .dp-td-val,
            #dapodik-hal-3 .dp-val-direct,
            #dapodik-hal-3 .dp-radio,
            #dapodik-hal-3 .dp-code-box {
                font-size: 7.3pt !important;
            }
            #dapodik-hal-3 .dp-section-bar {
                padding: 2.2px 6px !important;
                margin: 5px 0 3px 0 !important;
                font-size: 8.5pt !important;
                background: #0000ff !important;
                color: #ffffff !important;
                font-weight: bold !important;
                line-height: 1.15;
            }
            #dapodik-hal-3 .dp-sub-section-bar {
                padding: 2.2px 6px !important;
                margin: 6px 0 3px 0 !important;
                font-size: 8.5pt !important;
                background: #5ea3eb !important;
                color: #ffffff !important;
                font-weight: bold !important;
                line-height: 1.15;
                text-align: center;
            }
            #dapodik-hal-3 .dp-footer-page {
                position: absolute !important;
                bottom: 8mm !important;
                right: 10mm !important;
                text-align: right;
                font-size: 7.5pt;
                font-weight: bold;
            }
            @media print {
                #dapodik-hal-3 {
                    height: 305mm !important;
                    min-height: 305mm !important;
                    max-height: 305mm !important;
                    position: relative !important;
                }
                #dapodik-hal-3 .dp-footer-page {
                    position: absolute !important;
                    bottom: 0 !important;
                    right: 0 !important;
                }
            }
        </style>

        <div class="dapodik-form-wrapper">

            <!-- ═══════════════════════════════════════════════════════════════ -->
            <!-- HALAMAN 1 : DATA PRIBADI                                      -->
            <!-- ═══════════════════════════════════════════════════════════════ -->
            <div class="dapodik-page" id="dapodik-hal-1">
                
                <!-- Kop Unit Sekolah -->
                <div class="dp-header-kop">
                    <?php if (!empty($unit['logo'])): ?>
                        <img src="<?= $unit['logo'] ?>" alt="Logo Unit" class="dp-header-logo">
                    <?php endif; ?>
                    <div class="dp-header-info">
                        <div class="dp-header-title">
                            <?= strtoupper($unit['nama']) ?>
                        </div>
                        <div class="dp-header-meta">
                            NPSN : <?= $unit['npsn'] ?><br>
                            NAJSIT : <?= $unit['najsit'] ?>
                        </div>
                    </div>
                </div>

                <!-- Judul Formulir -->
                <div class="dp-title-bar">FORMULIR PESERTA DIDIK</div>

                <!-- Sub Bar: Tanggal & No Formulir -->
                <div class="dp-sub-bar">
                    <div>
                        Tanggal : <span class="dp-sub-box"><?= $tglFormulirD ?></span> / <span class="dp-sub-box"><?= $tglFormulirM ?></span> / <span class="dp-sub-box"><?= $tglFormulirY ?></span>
                    </div>
                    <div>
                        No Formulir : <span class="dp-sub-box" style="min-width: 120px;"><?= e($noFormulir) ?></span>
                    </div>
                </div>

                <!-- Section: DATA PRIBADI -->
                <div class="dp-section-bar">DATA PRIBADI</div>

                <table class="dp-table">
                    <!-- 1. Nama Lengkap -->
                    <tr>
                        <td class="dp-td-num">1.</td>
                        <td class="dp-td-label">Nama Lengkap</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-val-direct full"><?= e($namaLengkap) ?></span>
                            <div class="dp-desc">Nama peserta didik sesuai dokumen resmi yang berlaku (Akta atau Ijazah sebelumnya). Hanya bisa diubah melalui http://vervalpd.data.kemdikbud.go.id.</div>
                        </td>
                    </tr>

                    <!-- 2. Jenis Kelamin -->
                    <tr>
                        <td class="dp-td-num">2.</td>
                        <td class="dp-td-label">Jenis Kelamin</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-radio">
                                <span class="dp-radio-circle <?= $isLaki ? 'checked' : '' ?>"></span> Laki-Laki
                            </span>
                            <span class="dp-radio">
                                <span class="dp-radio-circle <?= !$isLaki ? 'checked' : '' ?>"></span> Perempuan
                            </span>
                            <div class="dp-desc">Jenis Kelamin Peserta didik</div>
                        </td>
                    </tr>

                    <!-- 3. NISN -->
                    <tr>
                        <td class="dp-td-num">3.</td>
                        <td class="dp-td-label">NISN</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-val-direct" style="min-width: 140px; font-family: monospace;"><?= e($nisn) ?></span>
                            <div class="dp-desc">Nomor Induk Siswa Nasional peserta didik (jika memiliki). Format 10 digit angka. http://nisn.data.kemdikbud.go.id/page/data</div>
                        </td>
                    </tr>

                    <!-- 4. NIK -->
                    <tr>
                        <td class="dp-td-num">4.</td>
                        <td class="dp-td-label">NIK / No. KITAS (Untuk WNA)</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-val-direct" style="min-width: 180px; font-family: monospace;"><?= e($nik) ?></span>
                            <div class="dp-desc">Nomor Induk Kependudukan yang tercantum pada Kartu Keluarga, Kartu Identitas Anak, atau KTP (format 16 digit angka).</div>
                        </td>
                    </tr>

                    <!-- 5. No KK -->
                    <tr>
                        <td class="dp-td-num">5.</td>
                        <td class="dp-td-label">No KK</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-val-direct" style="min-width: 180px; font-family: monospace;"><?= e($noKk) ?></span>
                        </td>
                    </tr>

                    <!-- 6. Tempat Lahir -->
                    <tr>
                        <td class="dp-td-num">6.</td>
                        <td class="dp-td-label">Tempat Lahir</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-val-direct full"><?= e($tempatLahir) ?></span>
                            <div class="dp-desc">Tempat lahir peserta didik sesuai dokumen resmi yang berlaku</div>
                        </td>
                    </tr>

                    <!-- 7. Tanggal Lahir -->
                    <tr>
                        <td class="dp-td-num">7.</td>
                        <td class="dp-td-label">Tanggal Lahir</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-code-box"><?= $tglLahirD ?></span> / <span class="dp-code-box"><?= $tglLahirM ?></span> / <span class="dp-code-box" style="min-width: 40px;"><?= $tglLahirY ?></span>
                            <div class="dp-desc">Tanggal lahir peserta didik sesuai dokumen resmi yang berlaku. Hanya bisa diubah melalui http://vervalpd.data.kemdikbud.go.id.</div>
                        </td>
                    </tr>

                    <!-- 8. No Registrasi Akta -->
                    <tr>
                        <td class="dp-td-num">8.</td>
                        <td class="dp-td-label">No Registrasi Akta Kelahiran</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-val-direct full"><?= e($noAkta) ?></span>
                            <div class="dp-desc">Nomor registrasi Akta Kelahiran tercantum pada bagian tengah atas lembar kutipan akta kelahiran</div>
                        </td>
                    </tr>

                    <!-- 9. Agama Kepercayaan -->
                    <tr>
                        <td class="dp-td-num">9.</td>
                        <td class="dp-td-label">Agama Kepercayaan</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-code-box"><?= $kodeAgama ?></span>
                            <span class="<?= $kodeAgama === '01' ? 'dp-opt-active' : 'dp-opt-muted' ?>">01) Islam</span> &nbsp;
                            <span class="<?= $kodeAgama === '02' ? 'dp-opt-active' : 'dp-opt-muted' ?>">02) Kristen/Protestan</span> &nbsp;
                            <span class="<?= $kodeAgama === '03' ? 'dp-opt-active' : 'dp-opt-muted' ?>">03) Katholik</span> &nbsp;
                            <span class="<?= $kodeAgama === '04' ? 'dp-opt-active' : 'dp-opt-muted' ?>">04) Hindu</span> &nbsp;
                            <span class="<?= $kodeAgama === '05' ? 'dp-opt-active' : 'dp-opt-muted' ?>">05) Budha</span> &nbsp;
                            <span class="<?= $kodeAgama === '06' ? 'dp-opt-active' : 'dp-opt-muted' ?>">06) Konghucu</span><br>
                            <span class="<?= $kodeAgama === '07' ? 'dp-opt-active' : 'dp-opt-muted' ?>" style="margin-left: 32px;">07) Kepercayaan Kepada Tuhan YME</span>
                        </td>
                    </tr>

                    <!-- 10. Kewarganegaraan -->
                    <tr>
                        <td class="dp-td-num">10.</td>
                        <td class="dp-td-label">Kewarganegaraan</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-radio">
                                <span class="dp-radio-circle checked"></span> Indonesia (WNI)
                            </span>
                            <span class="dp-radio">
                                <span class="dp-radio-circle"></span> Asing (WNA)
                            </span>
                            &nbsp;:&nbsp; Nama Negara <span class="dp-val-direct" style="min-width: 120px;">Indonesia</span>
                        </td>
                    </tr>

                    <!-- 11. Berkebutuhan Khusus -->
                    <tr>
                        <td class="dp-td-num">11.</td>
                        <td class="dp-td-label">Berkebutuhan Khusus</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-code-box">01</span>
                            <span class="dp-opt-active">01) Tidak</span> &nbsp;
                            <span class="dp-opt-muted">02) Netra (A)</span> &nbsp;
                            <span class="dp-opt-muted">03) Rungu (B)</span> &nbsp;
                            <span class="dp-opt-muted">04) Grahita ringan (C)</span> &nbsp;
                            <span class="dp-opt-muted">05) Grahita Sedang (C1)</span> &nbsp;
                            <span class="dp-opt-muted">06) Daksa Ringan (D)</span> &nbsp;
                            <span class="dp-opt-muted">07) Daksa Sedang (D1)</span> &nbsp;
                            <span class="dp-opt-muted">08) Laras (E)</span> &nbsp;
                            <span class="dp-opt-muted">09) Wicara (F)</span> &nbsp;
                            <span class="dp-opt-muted">10) Tuna ganda (G)</span> &nbsp;
                            <span class="dp-opt-muted">11) Hiperaktif (H)</span> &nbsp;
                            <span class="dp-opt-muted">12) Cerdas Istimewa (I)</span> &nbsp;
                            <span class="dp-opt-muted">13) Bakat Istimewa (J)</span> &nbsp;
                            <span class="dp-opt-muted">14) Kesulitan Belajar (K)</span> &nbsp;
                            <span class="dp-opt-muted">15) Narkoba (N)</span> &nbsp;
                            <span class="dp-opt-muted">16) Indigo (O)</span> &nbsp;
                            <span class="dp-opt-muted">17) Down Sindrome (P)</span> &nbsp;
                            <span class="dp-opt-muted">18) Autis (Q)</span>
                        </td>
                    </tr>

                    <!-- 12. Alamat Jalan -->
                    <tr>
                        <td class="dp-td-num">12.</td>
                        <td class="dp-td-label">Alamat Jalan</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-val-direct full"><?= e($alamat) ?></span>
                            <div class="dp-dotted-divider"></div>
                            <div class="dp-desc">Jalur tempat tinggal peserta didik, terdiri atas gang, kompleks, blok, nomor rumah, dan sebagainya selain informasi yang diminta oleh kolom-kolom yang lain pada bagian ini.<br>Sebagai contoh, peserta didik tinggal di sebuah kompleks perumahan Griya Adam yang berada pada Jalan Kemanggisan, dengan nomor rumah 4-C, di lingkungan RT 005 dan RW 011, Dusun Cempaka, Desa Salatiga. Maka dapat diisi dengan: Jl. Kemanggisan, Komp. Griya Adam, No. 4-C</div>
                            <div class="dp-dotted-divider"></div>
                        </td>
                    </tr>

                    <!-- 13. RT -->
                    <tr>
                        <td class="dp-td-num">13.</td>
                        <td class="dp-td-label">RT</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-val-direct" style="min-width: 60px;"><?= e($rt) ?></span>
                            <div class="dp-desc">Nomor RT tempat tinggal peserta didik saat ini. Dari contoh di atas, misalnya dapat diisi dengan angka 5</div>
                        </td>
                    </tr>

                    <!-- 14. RW -->
                    <tr>
                        <td class="dp-td-num">14.</td>
                        <td class="dp-td-label">RW</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-val-direct" style="min-width: 60px;"><?= e($rw) ?></span>
                            <div class="dp-desc">Nomor RW tempat tinggal peserta didik saat ini. Dari contoh di atas, misalnya dapat diisi dengan angka 11</div>
                        </td>
                    </tr>

                    <!-- 15. Nama Dusun -->
                    <tr>
                        <td class="dp-td-num">15.</td>
                        <td class="dp-td-label">Nama Dusun</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-val-direct full"><?= e($dusun) ?></span>
                            <div class="dp-desc">Nama dusun tempat tinggal peserta didik saat ini. Dari contoh di atas, misalnya dapat diisi dengan Cempak</div>
                        </td>
                    </tr>

                    <!-- 16. Nama Kelurahan/Desa -->
                    <tr>
                        <td class="dp-td-num">16.</td>
                        <td class="dp-td-label">Nama Kelurahan/ Desa</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-val-direct full"><?= e($kelurahan) ?></span>
                            <div class="dp-desc">Nama desa atau Kelurahan tempat tinggal peserta didik saat ini. Dari contoh diatas, misalnya dapat diisi dengan Cempak</div>
                        </td>
                    </tr>

                    <!-- 17. Kecamatan -->
                    <tr>
                        <td class="dp-td-num">17.</td>
                        <td class="dp-td-label">Kecamatan</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-val-direct full"><?= e($kecamatan) ?></span>
                            <div class="dp-desc">Kecamatan tempat tinggal peserta didik saat ini</div>
                        </td>
                    </tr>

                    <!-- 18. Kode Pos -->
                    <tr>
                        <td class="dp-td-num">18.</td>
                        <td class="dp-td-label">Kode Pos</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-val-direct" style="min-width: 80px; font-family: monospace;"><?= e($kodePos) ?></span>
                            <div class="dp-desc">Kode Pos tempat tinggal peserta didik saat ini</div>
                        </td>
                    </tr>

                    <!-- 19. Lintang -->
                    <tr>
                        <td class="dp-td-num">19.</td>
                        <td class="dp-td-label">Lintang</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-val-direct" style="min-width: 140px; font-family: monospace;"><?= e($lintang) ?></span>
                            <div class="dp-desc">Titik Koordinat Tempat Tinggal Siswa</div>
                        </td>
                    </tr>

                    <!-- 20. Bujur -->
                    <tr>
                        <td class="dp-td-num">20.</td>
                        <td class="dp-td-label">Bujur</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-val-direct" style="min-width: 140px; font-family: monospace;"><?= e($bujur) ?></span>
                            <div class="dp-desc">Titik Koordinat Tempat Tinggal Siswa</div>
                        </td>
                    </tr>

                    <!-- 21. Tempat Tinggal -->
                    <tr>
                        <td class="dp-td-num">21.</td>
                        <td class="dp-td-label">Tempat Tinggal</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-code-box"><?= $kodeTinggal ?></span>
                            <span class="<?= $kodeTinggal === '01' ? 'dp-opt-active' : 'dp-opt-muted' ?>">01) Bersama orang tua</span> &nbsp;
                            <span class="<?= $kodeTinggal === '02' ? 'dp-opt-active' : 'dp-opt-muted' ?>">02) Wali</span> &nbsp;
                            <span class="<?= $kodeTinggal === '03' ? 'dp-opt-active' : 'dp-opt-muted' ?>">03) Kos</span> &nbsp;
                            <span class="<?= $kodeTinggal === '04' ? 'dp-opt-active' : 'dp-opt-muted' ?>">04) Asrama</span> &nbsp;
                            <span class="<?= $kodeTinggal === '05' ? 'dp-opt-active' : 'dp-opt-muted' ?>">05) Panti Asuhan</span>
                            <div class="dp-desc">Tempat Tinggal Siswa</div>
                        </td>
                    </tr>

                    <!-- 22. Moda Transportasi -->
                    <tr>
                        <td class="dp-td-num">22.</td>
                        <td class="dp-td-label">Moda Transportasi</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-code-box"><?= $kodeTransport ?></span>
                            <span class="<?= $kodeTransport === '01' ? 'dp-opt-active' : 'dp-opt-muted' ?>">01) Jalan kaki</span> &nbsp;
                            <span class="<?= $kodeTransport === '02' ? 'dp-opt-active' : 'dp-opt-muted' ?>">02) Kendaraan pribadi</span> &nbsp;
                            <span class="<?= $kodeTransport === '03' ? 'dp-opt-active' : 'dp-opt-muted' ?>">03) Kendaraan Umum/angkot/Pete-pete</span> &nbsp;
                            <span class="<?= $kodeTransport === '04' ? 'dp-opt-active' : 'dp-opt-muted' ?>">04) Jemputan Sekolah</span> &nbsp;
                            <span class="<?= $kodeTransport === '05' ? 'dp-opt-active' : 'dp-opt-muted' ?>">05) Kereta Api</span><br>
                            <span class="<?= $kodeTransport === '06' ? 'dp-opt-active' : 'dp-opt-muted' ?>">06) Ojek</span> &nbsp;
                            <span class="<?= $kodeTransport === '07' ? 'dp-opt-active' : 'dp-opt-muted' ?>">07) Andong/Bendi/Sado/Dokar/Delman/Becak</span> &nbsp;
                            <span class="<?= $kodeTransport === '08' ? 'dp-opt-active' : 'dp-opt-muted' ?>">08) Perahu penyebrangan/Rakit/Getek</span> &nbsp;
                            <span class="<?= $kodeTransport === '09' ? 'dp-opt-active' : 'dp-opt-muted' ?>">09) Lainnya</span>
                            <div class="dp-desc">Jenis transportasi utama atau yang paling sering digunakan peserta didik untuk berangkat ke sekolah</div>
                        </td>
                    </tr>

                    <!-- 23. Anak Keberapa -->
                    <tr>
                        <td class="dp-td-num">23.</td>
                        <td class="dp-td-label">Anak Keberapa</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-code-box"><?= str_pad((string)$anakKe, 2, '0', STR_PAD_LEFT) ?></span>
                            <div class="dp-desc">Sesuaikan dengan urutan pada Kartu Keluarga</div>
                        </td>
                    </tr>

                    <!-- 24. Alergi -->
                    <tr>
                        <td class="dp-td-num">24.</td>
                        <td class="dp-td-label">Alergi</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-radio">
                                <span class="dp-radio-circle <?= $hasAlergi ? 'checked' : '' ?>"></span> Ya
                            </span>
                            <span class="dp-radio">
                                <span class="dp-radio-circle <?= !$hasAlergi ? 'checked' : '' ?>"></span> Tidak
                            </span>
                            <?php if ($hasAlergi): ?>
                                &nbsp;&nbsp; (Nama Alergi: <strong><?= e($namaAlergi) ?></strong>)
                            <?php endif; ?>
                            <div class="dp-desc">Pilih Ya apabila peserta didik memiliki alergi dan tuliskan alerginya</div>
                            <div class="dp-dotted-divider" style="margin-top: 6px;"></div>
                        </td>
                    </tr>
                </table>

                <div class="dp-footer-page">Halaman 1</div>
            </div>


            <!-- ═══════════════════════════════════════════════════════════════ -->
            <!-- HALAMAN 2 : DATA ORANG TUA KANDUNG & WALI                      -->
            <!-- ═══════════════════════════════════════════════════════════════ -->
            <div class="dapodik-page" id="dapodik-hal-2">
                <table class="dp-table">
                    <!-- 25. Pekerjaan (diperuntukkan untuk warga belajar) -->
                    <tr>
                        <td class="dp-td-num">25.</td>
                        <td class="dp-td-label">Pekerjaan (diperuntukkan untuk warga belajar)</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-code-box">&nbsp;&nbsp;</span>
                            <span class="dp-opt-muted">01) Tidak bekerja</span> &nbsp;
                            <span class="dp-opt-muted">02) Nelayan</span> &nbsp;
                            <span class="dp-opt-muted">03) Petani</span> &nbsp;
                            <span class="dp-opt-muted">04) Peternak</span> &nbsp;
                            <span class="dp-opt-muted">05) PNS/TNI/POLRI</span> &nbsp;
                            <span class="dp-opt-muted">06) Karyawan Swasta</span> &nbsp;
                            <span class="dp-opt-muted">07) Pedagang Kecil</span> &nbsp;
                            <span class="dp-opt-muted">08) Pedagang Besar</span> &nbsp;
                            <span class="dp-opt-muted">09) Wiraswasta</span> &nbsp;
                            <span class="dp-opt-muted">10) Wirausaha</span> &nbsp;
                            <span class="dp-opt-muted">11) Buruh</span> &nbsp;
                            <span class="dp-opt-muted">12) Pensiunan</span>
                        </td>
                    </tr>

                    <!-- 26. Apakah punya KIP -->
                    <tr>
                        <td class="dp-td-num">26.</td>
                        <td class="dp-td-label">Apakah punya KIP</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-radio"><span class="dp-radio-circle"></span> Ya</span>
                            <span class="dp-radio"><span class="dp-radio-circle checked"></span> Tidak</span>
                            <div class="dp-desc">Pilih Ya apabila peserta didik memiliki Kartu Indonesia Pintar (KIP). Pilih Tidak jika tidak memiliki.</div>
                        </td>
                    </tr>

                    <!-- 27. Apakah peserta didik tersebut tetap menerima KIP -->
                    <tr>
                        <td class="dp-td-num">27.</td>
                        <td class="dp-td-label">Apakah peserta didik tersebut tetap menerima KIP</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-radio"><span class="dp-radio-circle"></span> Ya</span>
                            <span class="dp-radio"><span class="dp-radio-circle checked"></span> Tidak</span>
                            <div class="dp-desc">Status bahwa peserta didik sudah menerima atau belum menerima Kartu Indonesia Pintar secara fisik</div>
                        </td>
                    </tr>

                    <!-- 28. Alasan menolak PIP -->
                    <tr>
                        <td class="dp-td-num">28.</td>
                        <td class="dp-td-label">Alasan menolak PIP</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-code-box">&nbsp;&nbsp;</span>
                            <span class="dp-opt-muted">1) Dilarang pemda karena menerima bantuan serupa</span> &nbsp;
                            <span class="dp-opt-muted">02) Menolak</span> &nbsp;
                            <span class="dp-opt-muted">03) Sudah mampu</span>
                            <div class="dp-desc">Alasan utama peserta didik jika layak menerima manfaat PIP. Kolom ini akan muncul apabila dipilih Ya untuk mengisi kolom Usulan dari Sekolah (Layak PIP)</div>
                        </td>
                    </tr>

                    <!-- SECTION: DATA AYAH KANDUNG -->
                    <tr>
                        <td colspan="4" style="padding: 0;">
                            <div class="dp-section-bar">DATA AYAH KANDUNG</div>
                        </td>
                    </tr>

                    <!-- 29. Nama ayah kandung -->
                    <tr>
                        <td class="dp-td-num">29.</td>
                        <td class="dp-td-label">Nama ayah kandung</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-val-direct full"><?= !empty($namaAyah) && $namaAyah !== '-' ? e($namaAyah) : '&nbsp;' ?></span>
                            <div class="dp-desc">Nama ayah kandung peserta didik sesuai dokumen resmi yang berlaku. Hindari penggunaan gelar akademik atau sosial (seperti Alm., Dr., Drs., S.Pd, dan H.).</div>
                        </td>
                    </tr>

                    <!-- 30. NIK ayah -->
                    <tr>
                        <td class="dp-td-num">30.</td>
                        <td class="dp-td-label">NIK ayah</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-val-direct" style="min-width: 180px; font-family: monospace;"><?= !empty($nikAyah) && $nikAyah !== '-' ? e($nikAyah) : '&nbsp;' ?></span>
                            <div class="dp-desc">Nomor Induk Kependudukan yang tercantum pada Kartu Keluarga atau KTP ayah kandung peserta didik</div>
                        </td>
                    </tr>

                    <!-- 31. Tahun Lahir -->
                    <tr>
                        <td class="dp-td-num">31.</td>
                        <td class="dp-td-label">Tahun Lahir</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-code-box" style="min-width: 44px; font-family: monospace;"><?= !empty($thnAyah) && $thnAyah !== '-' ? e($thnAyah) : '&nbsp;&nbsp;&nbsp;&nbsp;' ?></span>
                            <div class="dp-desc">Tahun lahir ayah kandung peserta didik</div>
                        </td>
                    </tr>

                    <!-- 32. Pendidikan -->
                    <tr>
                        <td class="dp-td-num">32.</td>
                        <td class="dp-td-label">Pendidikan</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-code-box"><?= $kodePendAyah ?></span>
                            <span class="<?= $kodePendAyah === '01' ? 'dp-opt-active' : 'dp-opt-muted' ?>">01) Tidak sekolah</span> &nbsp;
                            <span class="<?= $kodePendAyah === '02' ? 'dp-opt-active' : 'dp-opt-muted' ?>">02) Putus SD</span> &nbsp;
                            <span class="<?= $kodePendAyah === '03' ? 'dp-opt-active' : 'dp-opt-muted' ?>">03) SD Sederajat</span> &nbsp;
                            <span class="<?= $kodePendAyah === '04' ? 'dp-opt-active' : 'dp-opt-muted' ?>">04) SMP Sederajat</span> &nbsp;
                            <span class="<?= $kodePendAyah === '05' ? 'dp-opt-active' : 'dp-opt-muted' ?>">05) SMA Sederajat</span> &nbsp;
                            <span class="<?= $kodePendAyah === '06' ? 'dp-opt-active' : 'dp-opt-muted' ?>">06) D1</span> &nbsp;
                            <span class="<?= $kodePendAyah === '07' ? 'dp-opt-active' : 'dp-opt-muted' ?>">07) D2</span> &nbsp;
                            <span class="<?= $kodePendAyah === '08' ? 'dp-opt-active' : 'dp-opt-muted' ?>">08) D3</span> &nbsp;
                            <span class="<?= $kodePendAyah === '09' ? 'dp-opt-active' : 'dp-opt-muted' ?>">09) D4/S1</span> &nbsp;
                            <span class="<?= $kodePendAyah === '10' ? 'dp-opt-active' : 'dp-opt-muted' ?>">10) S2</span> &nbsp;
                            <span class="<?= $kodePendAyah === '11' ? 'dp-opt-active' : 'dp-opt-muted' ?>">11) S3</span>
                            <div class="dp-desc">Pendidikan terakhir ayah kandung peserta didik</div>
                        </td>
                    </tr>

                    <!-- 33. Pekerjaan -->
                    <tr>
                        <td class="dp-td-num">33.</td>
                        <td class="dp-td-label">Pekerjaan</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-code-box"><?= $kodePekAyah ?></span>
                            <span class="<?= $kodePekAyah === '01' ? 'dp-opt-active' : 'dp-opt-muted' ?>">01) Tidak bekerja</span> &nbsp;
                            <span class="<?= $kodePekAyah === '02' ? 'dp-opt-active' : 'dp-opt-muted' ?>">02) Nelayan</span> &nbsp;
                            <span class="<?= $kodePekAyah === '03' ? 'dp-opt-active' : 'dp-opt-muted' ?>">03) Petani</span> &nbsp;
                            <span class="<?= $kodePekAyah === '04' ? 'dp-opt-active' : 'dp-opt-muted' ?>">04) Peternak</span> &nbsp;
                            <span class="<?= $kodePekAyah === '05' ? 'dp-opt-active' : 'dp-opt-muted' ?>">05) PNS/TNI/POLRI</span> &nbsp;
                            <span class="<?= $kodePekAyah === '06' ? 'dp-opt-active' : 'dp-opt-muted' ?>">06) Karyawan Swasta</span> &nbsp;
                            <span class="<?= $kodePekAyah === '07' ? 'dp-opt-active' : 'dp-opt-muted' ?>">07) Pedagang Kecil</span> &nbsp;
                            <span class="<?= $kodePekAyah === '08' ? 'dp-opt-active' : 'dp-opt-muted' ?>">08) Pedagang Besar</span> &nbsp;
                            <span class="<?= $kodePekAyah === '09' ? 'dp-opt-active' : 'dp-opt-muted' ?>">09) Wiraswasta</span> &nbsp;
                            <span class="<?= $kodePekAyah === '10' ? 'dp-opt-active' : 'dp-opt-muted' ?>">10) Wirausaha</span> &nbsp;
                            <span class="<?= $kodePekAyah === '11' ? 'dp-opt-active' : 'dp-opt-muted' ?>">11) Buruh</span> &nbsp;
                            <span class="<?= $kodePekAyah === '12' ? 'dp-opt-active' : 'dp-opt-muted' ?>">12) Pensiunan</span>
                            <div class="dp-desc">Pekerjaan ayah kandung peserta didik</div>
                        </td>
                    </tr>

                    <!-- 34. Penghasilan Bulanan -->
                    <tr>
                        <td class="dp-td-num">34.</td>
                        <td class="dp-td-label">Penghasilan Bulanan</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-code-box"><?= $kodePengAyah ?></span>
                            <span class="<?= $kodePengAyah === '01' ? 'dp-opt-active' : 'dp-opt-muted' ?>">01) &lt; Rp. 500.000</span> &nbsp;
                            <span class="<?= $kodePengAyah === '02' ? 'dp-opt-active' : 'dp-opt-muted' ?>">02) Rp. 500.000-Rp.999.999</span> &nbsp;
                            <span class="<?= $kodePengAyah === '03' ? 'dp-opt-active' : 'dp-opt-muted' ?>">03) Rp. 1.000.000-Rp.1.999.999</span> &nbsp;
                            <span class="<?= $kodePengAyah === '04' ? 'dp-opt-active' : 'dp-opt-muted' ?>">04) Rp.2.000.000-Rp.4.999.999</span> &nbsp;
                            <span class="<?= $kodePengAyah === '05' ? 'dp-opt-active' : 'dp-opt-muted' ?>">05) Rp.5.000.000-Rp.20.000.000</span> &nbsp;
                            <span class="<?= $kodePengAyah === '06' ? 'dp-opt-active' : 'dp-opt-muted' ?>">06) &gt; Rp.20.000.000</span> &nbsp;
                            <span class="<?= $kodePengAyah === '07' ? 'dp-opt-active' : 'dp-opt-muted' ?>">07) Tidak Berpenghasilan</span>
                            <div class="dp-desc">Penghasilan Bulanan ayah kandung peserta didik</div>
                        </td>
                    </tr>

                    <!-- 35. Berkebutuhan Khusus -->
                    <tr>
                        <td class="dp-td-num">35.</td>
                        <td class="dp-td-label">Berkebutuhan Khusus</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-code-box">01</span>
                            <span class="dp-opt-active">1) Tidak</span> &nbsp;
                            <span class="dp-opt-muted">02) Netra (A)</span> &nbsp;
                            <span class="dp-opt-muted">03) Rungu (B)</span> &nbsp;
                            <span class="dp-opt-muted">04) Grahita ringan (C)</span> &nbsp;
                            <span class="dp-opt-muted">05) Grahita Sedang (C1)</span> &nbsp;
                            <span class="dp-opt-muted">06) Daksa Ringan (D)</span> &nbsp;
                            <span class="dp-opt-muted">07) Daksa Sedang (D1)</span> &nbsp;
                            <span class="dp-opt-muted">08) 09) Wicara (F)</span> &nbsp;
                            <span class="dp-opt-muted">10) Tuna ganda (G)</span> &nbsp;
                            <span class="dp-opt-muted">11) Hiper aktif (H)</span> &nbsp;
                            <span class="dp-opt-muted">12) Cerdas Istimewa (i)</span> &nbsp;
                            <span class="dp-opt-muted">13) Bakat Istimewa (J)</span> &nbsp;
                            <span class="dp-opt-muted">14) Kesulitan Belajra (K)</span> &nbsp;
                            <span class="dp-opt-muted">15) Narkoba (N)</span> &nbsp;
                            <span class="dp-opt-muted">16) Indigo (O)</span> &nbsp;
                            <span class="dp-opt-muted">17) Down Sindrome (P)</span> &nbsp;
                            <span class="dp-opt-muted">18) Autis (Q)</span>
                            <div class="dp-desc">Kebutuhan khusus yang disandang oleh ayah peserta didik. Dapat dipilih lebih dari satu</div>
                        </td>
                    </tr>

                    <!-- SECTION: DATA IBU KANDUNG -->
                    <tr>
                        <td colspan="4" style="padding: 0;">
                            <div class="dp-section-bar">DATA IBU KANDUNG</div>
                        </td>
                    </tr>

                    <!-- 36. Nama Ibu kandung -->
                    <tr>
                        <td class="dp-td-num">36.</td>
                        <td class="dp-td-label">Nama Ibu kandung</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-val-direct full"><?= !empty($namaIbu) && $namaIbu !== '-' ? e($namaIbu) : '&nbsp;' ?></span>
                            <div class="dp-desc">Nama ibu kandung peserta didik sesuai dokumen resmi yang berlaku. Hindari penggunaan gelar akademik atau sosial (seperti Alm., Dr., Dra., S.Pd, dan Hj.).</div>
                        </td>
                    </tr>

                    <!-- 37. NIK Ibu -->
                    <tr>
                        <td class="dp-td-num">37.</td>
                        <td class="dp-td-label">NIK Ibu</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-val-direct" style="min-width: 180px; font-family: monospace;"><?= !empty($nikIbu) && $nikIbu !== '-' ? e($nikIbu) : '&nbsp;' ?></span>
                            <div class="dp-desc">Nomor Induk Kependudukan yang tercantum pada Kartu Keluarga atau KTP Ibu kandung peserta didik</div>
                        </td>
                    </tr>

                    <!-- 38. Tahun Lahir -->
                    <tr>
                        <td class="dp-td-num">38.</td>
                        <td class="dp-td-label">Tahun Lahir</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-code-box" style="min-width: 44px; font-family: monospace;"><?= !empty($thnIbu) && $thnIbu !== '-' ? e($thnIbu) : '&nbsp;&nbsp;&nbsp;&nbsp;' ?></span>
                            <div class="dp-desc">Tahun lahir Ibu kandung peserta didik</div>
                        </td>
                    </tr>

                    <!-- 39. Pendidikan -->
                    <tr>
                        <td class="dp-td-num">39.</td>
                        <td class="dp-td-label">Pendidikan</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-code-box"><?= $kodePendIbu ?></span>
                            <span class="<?= $kodePendIbu === '01' ? 'dp-opt-active' : 'dp-opt-muted' ?>">01) Tidak sekolah</span> &nbsp;
                            <span class="<?= $kodePendIbu === '02' ? 'dp-opt-active' : 'dp-opt-muted' ?>">02) Putus SD</span> &nbsp;
                            <span class="<?= $kodePendIbu === '03' ? 'dp-opt-active' : 'dp-opt-muted' ?>">03) SD Sederajat</span> &nbsp;
                            <span class="<?= $kodePendIbu === '04' ? 'dp-opt-active' : 'dp-opt-muted' ?>">04) SMP Sederajat</span> &nbsp;
                            <span class="<?= $kodePendIbu === '05' ? 'dp-opt-active' : 'dp-opt-muted' ?>">05) SMA Sederajat</span> &nbsp;
                            <span class="<?= $kodePendIbu === '06' ? 'dp-opt-active' : 'dp-opt-muted' ?>">06) D1</span> &nbsp;
                            <span class="<?= $kodePendIbu === '07' ? 'dp-opt-active' : 'dp-opt-muted' ?>">07) D2</span> &nbsp;
                            <span class="<?= $kodePendIbu === '08' ? 'dp-opt-active' : 'dp-opt-muted' ?>">08) D3</span> &nbsp;
                            <span class="<?= $kodePendIbu === '09' ? 'dp-opt-active' : 'dp-opt-muted' ?>">09) D4/S1</span> &nbsp;
                            <span class="<?= $kodePendIbu === '10' ? 'dp-opt-active' : 'dp-opt-muted' ?>">10) S2</span> &nbsp;
                            <span class="<?= $kodePendIbu === '11' ? 'dp-opt-active' : 'dp-opt-muted' ?>">11) S3</span>
                            <div class="dp-desc">Pendidikan terakhir ibu kandung peserta didik</div>
                        </td>
                    </tr>

                    <!-- 40. Pekerjaan -->
                    <tr>
                        <td class="dp-td-num">40.</td>
                        <td class="dp-td-label">Pekerjaan</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-code-box"><?= $kodePekIbu ?></span>
                            <span class="<?= $kodePekIbu === '01' ? 'dp-opt-active' : 'dp-opt-muted' ?>">01) Tidak bekerja</span> &nbsp;
                            <span class="<?= $kodePekIbu === '02' ? 'dp-opt-active' : 'dp-opt-muted' ?>">02) Nelayan</span> &nbsp;
                            <span class="<?= $kodePekIbu === '03' ? 'dp-opt-active' : 'dp-opt-muted' ?>">03) Petani</span> &nbsp;
                            <span class="<?= $kodePekIbu === '04' ? 'dp-opt-active' : 'dp-opt-muted' ?>">04) Peternak</span> &nbsp;
                            <span class="<?= $kodePekIbu === '05' ? 'dp-opt-active' : 'dp-opt-muted' ?>">05) PNS/TNI/POLRI</span> &nbsp;
                            <span class="<?= $kodePekIbu === '06' ? 'dp-opt-active' : 'dp-opt-muted' ?>">06) Karyawan Swasta</span> &nbsp;
                            <span class="<?= $kodePekIbu === '07' ? 'dp-opt-active' : 'dp-opt-muted' ?>">07) Pedagang Kecil</span> &nbsp;
                            <span class="<?= $kodePekIbu === '08' ? 'dp-opt-active' : 'dp-opt-muted' ?>">08) Pedagang Besar</span> &nbsp;
                            <span class="<?= $kodePekIbu === '09' ? 'dp-opt-active' : 'dp-opt-muted' ?>">09) Wiraswasta</span> &nbsp;
                            <span class="<?= $kodePekIbu === '10' ? 'dp-opt-active' : 'dp-opt-muted' ?>">10) Wirausaha</span> &nbsp;
                            <span class="<?= $kodePekIbu === '11' ? 'dp-opt-active' : 'dp-opt-muted' ?>">11) Buruh</span> &nbsp;
                            <span class="<?= $kodePekIbu === '12' ? 'dp-opt-active' : 'dp-opt-muted' ?>">12) Pensiunan</span>
                            <div class="dp-desc">Pekerjaan ibu kandung peserta didik</div>
                        </td>
                    </tr>

                    <!-- 41. Penghasilan Bulanan -->
                    <tr>
                        <td class="dp-td-num">41.</td>
                        <td class="dp-td-label">Penghasilan Bulanan</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-code-box"><?= $kodePengIbu ?></span>
                            <span class="<?= $kodePengIbu === '01' ? 'dp-opt-active' : 'dp-opt-muted' ?>">01) &lt; Rp. 500.000</span> &nbsp;
                            <span class="<?= $kodePengIbu === '02' ? 'dp-opt-active' : 'dp-opt-muted' ?>">02) Rp. 500.000-Rp.999.999</span> &nbsp;
                            <span class="<?= $kodePengIbu === '03' ? 'dp-opt-active' : 'dp-opt-muted' ?>">03) Rp. 1.000.000-Rp.1.999.999</span> &nbsp;
                            <span class="<?= $kodePengIbu === '04' ? 'dp-opt-active' : 'dp-opt-muted' ?>">04) Rp.2.000.000-Rp.4.999.999</span> &nbsp;
                            <span class="<?= $kodePengIbu === '05' ? 'dp-opt-active' : 'dp-opt-muted' ?>">05) Rp.5.000.000-Rp.20.000.000</span> &nbsp;
                            <span class="<?= $kodePengIbu === '06' ? 'dp-opt-active' : 'dp-opt-muted' ?>">06) &gt; Rp.20.000.000</span> &nbsp;
                            <span class="<?= $kodePengIbu === '07' ? 'dp-opt-active' : 'dp-opt-muted' ?>">07) Tidak Berpenghasilan</span>
                            <div class="dp-desc">Penghasilan Bulanan ibu kandung peserta didik</div>
                        </td>
                    </tr>

                    <!-- 42. Berkebutuhan Khusus -->
                    <tr>
                        <td class="dp-td-num">42.</td>
                        <td class="dp-td-label">Berkebutuhan Khusus</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-code-box">01</span>
                            <span class="dp-opt-active">1) Tidak</span> &nbsp;
                            <span class="dp-opt-muted">02) Netra (A)</span> &nbsp;
                            <span class="dp-opt-muted">03) Rungu (B)</span> &nbsp;
                            <span class="dp-opt-muted">04) Grahita ringan (C)</span> &nbsp;
                            <span class="dp-opt-muted">05) Grahita Sedang (C1)</span> &nbsp;
                            <span class="dp-opt-muted">06) Daksa Ringan (D)</span> &nbsp;
                            <span class="dp-opt-muted">07) Daksa Sedang (D1)</span> &nbsp;
                            <span class="dp-opt-muted">08) 09) Wicara (F)</span> &nbsp;
                            <span class="dp-opt-muted">10) Tuna ganda (G)</span> &nbsp;
                            <span class="dp-opt-muted">11) Hiper aktif (H)</span> &nbsp;
                            <span class="dp-opt-muted">12) Cerdas Istimewa (i)</span> &nbsp;
                            <span class="dp-opt-muted">13) Bakat Istimewa (J)</span> &nbsp;
                            <span class="dp-opt-muted">14) Kesulitan Belajra (K)</span> &nbsp;
                            <span class="dp-opt-muted">15) Narkoba (N)</span> &nbsp;
                            <span class="dp-opt-muted">16) Indigo (O)</span> &nbsp;
                            <span class="dp-opt-muted">17) Down Sindrome (P)</span> &nbsp;
                            <span class="dp-opt-muted">18) Autis (Q)</span>
                            <div class="dp-desc">Kebutuhan khusus yang disandang oleh ibu peserta didik. Dapat dipilih lebih dari satu</div>
                        </td>
                    </tr>

                    <!-- SECTION: DATA WALI -->
                    <tr>
                        <td colspan="4" style="padding: 0;">
                            <div class="dp-section-bar">DATA WALI</div>
                        </td>
                    </tr>

                    <!-- 43. Nama -->
                    <tr>
                        <td class="dp-td-num">43.</td>
                        <td class="dp-td-label">Nama</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-val-direct full"><?= !empty($namaWali) && $namaWali !== '-' ? e($namaWali) : '&nbsp;' ?></span>
                            <div class="dp-desc">Nama wali peserta didik sesuai dokumen resmi yang berlaku. Hindari penggunaan gelar akademik atau sosial (seperti Alm., Dr., Drs., S.Pd, dan H.).</div>
                        </td>
                    </tr>

                    <!-- 44. NIK -->
                    <tr>
                        <td class="dp-td-num">44.</td>
                        <td class="dp-td-label">NIK</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-val-direct" style="min-width: 180px; font-family: monospace;"><?= !empty($nikWali) && $nikWali !== '-' ? e($nikWali) : '&nbsp;' ?></span>
                            <div class="dp-desc">Nomor Induk Kependudukan yang tercantum pada Kartu Keluarga atau KTP Wali peserta didik</div>
                        </td>
                    </tr>

                    <!-- 45. Tahun Lahir -->
                    <tr>
                        <td class="dp-td-num">45.</td>
                        <td class="dp-td-label">Tahun Lahir</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-code-box" style="min-width: 44px; font-family: monospace;"><?= !empty($thnWali) && $thnWali !== '-' ? e($thnWali) : '&nbsp;&nbsp;&nbsp;&nbsp;' ?></span>
                            <div class="dp-desc">Tahun lahir wali peserta didik</div>
                        </td>
                    </tr>

                    <!-- 46. Pendidikan -->
                    <tr>
                        <td class="dp-td-num">46.</td>
                        <td class="dp-td-label">Pendidikan</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-code-box"><?= !empty($data['nama_wali']) ? $kodePendWali : '&nbsp;&nbsp;' ?></span>
                            <span class="<?= !empty($data['nama_wali']) && $kodePendWali === '01' ? 'dp-opt-active' : 'dp-opt-muted' ?>">01) Tidak sekolah</span> &nbsp;
                            <span class="<?= !empty($data['nama_wali']) && $kodePendWali === '02' ? 'dp-opt-active' : 'dp-opt-muted' ?>">02) Putus SD</span> &nbsp;
                            <span class="<?= !empty($data['nama_wali']) && $kodePendWali === '03' ? 'dp-opt-active' : 'dp-opt-muted' ?>">03) SD Sederajat</span> &nbsp;
                            <span class="<?= !empty($data['nama_wali']) && $kodePendWali === '04' ? 'dp-opt-active' : 'dp-opt-muted' ?>">04) SMP Sederajat</span> &nbsp;
                            <span class="<?= !empty($data['nama_wali']) && $kodePendWali === '05' ? 'dp-opt-active' : 'dp-opt-muted' ?>">05) SMA Sederajat</span> &nbsp;
                            <span class="<?= !empty($data['nama_wali']) && $kodePendWali === '06' ? 'dp-opt-active' : 'dp-opt-muted' ?>">06) D1</span> &nbsp;
                            <span class="<?= !empty($data['nama_wali']) && $kodePendWali === '07' ? 'dp-opt-active' : 'dp-opt-muted' ?>">07) D2</span> &nbsp;
                            <span class="<?= !empty($data['nama_wali']) && $kodePendWali === '08' ? 'dp-opt-active' : 'dp-opt-muted' ?>">08) D3</span> &nbsp;
                            <span class="<?= !empty($data['nama_wali']) && $kodePendWali === '09' ? 'dp-opt-active' : 'dp-opt-muted' ?>">09) D4/S1</span> &nbsp;
                            <span class="<?= !empty($data['nama_wali']) && $kodePendWali === '10' ? 'dp-opt-active' : 'dp-opt-muted' ?>">10) S2</span> &nbsp;
                            <span class="<?= !empty($data['nama_wali']) && $kodePendWali === '11' ? 'dp-opt-active' : 'dp-opt-muted' ?>">11) S3</span>
                            <div class="dp-desc">Pendidikan terakhir wali peserta didik</div>
                        </td>
                    </tr>

                    <!-- 47. Pekerjaan -->
                    <tr>
                        <td class="dp-td-num">47.</td>
                        <td class="dp-td-label">Pekerjaan</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-code-box"><?= !empty($data['nama_wali']) ? $kodePekWali : '&nbsp;&nbsp;' ?></span>
                            <span class="<?= !empty($data['nama_wali']) && $kodePekWali === '01' ? 'dp-opt-active' : 'dp-opt-muted' ?>">01) Tidak bekerja</span> &nbsp;
                            <span class="<?= !empty($data['nama_wali']) && $kodePekWali === '02' ? 'dp-opt-active' : 'dp-opt-muted' ?>">02) Nelayan</span> &nbsp;
                            <span class="<?= !empty($data['nama_wali']) && $kodePekWali === '03' ? 'dp-opt-active' : 'dp-opt-muted' ?>">03) Petani</span> &nbsp;
                            <span class="<?= !empty($data['nama_wali']) && $kodePekWali === '04' ? 'dp-opt-active' : 'dp-opt-muted' ?>">04) Peternak</span> &nbsp;
                            <span class="<?= !empty($data['nama_wali']) && $kodePekWali === '05' ? 'dp-opt-active' : 'dp-opt-muted' ?>">05) PNS/TNI/POLRI</span> &nbsp;
                            <span class="<?= !empty($data['nama_wali']) && $kodePekWali === '06' ? 'dp-opt-active' : 'dp-opt-muted' ?>">06) Karyawan Swasta</span> &nbsp;
                            <span class="<?= !empty($data['nama_wali']) && $kodePekWali === '07' ? 'dp-opt-active' : 'dp-opt-muted' ?>">07) Pedagang Kecil</span> &nbsp;
                            <span class="<?= !empty($data['nama_wali']) && $kodePekWali === '08' ? 'dp-opt-active' : 'dp-opt-muted' ?>">08) Pedagang Besar</span> &nbsp;
                            <span class="<?= !empty($data['nama_wali']) && $kodePekWali === '09' ? 'dp-opt-active' : 'dp-opt-muted' ?>">09) Wiraswasta</span> &nbsp;
                            <span class="<?= !empty($data['nama_wali']) && $kodePekWali === '10' ? 'dp-opt-active' : 'dp-opt-muted' ?>">10) Wirausaha</span> &nbsp;
                            <span class="<?= !empty($data['nama_wali']) && $kodePekWali === '11' ? 'dp-opt-active' : 'dp-opt-muted' ?>">11) Buruh</span> &nbsp;
                            <span class="<?= !empty($data['nama_wali']) && $kodePekWali === '12' ? 'dp-opt-active' : 'dp-opt-muted' ?>">12) Pensiunan</span>
                            <div class="dp-desc">Pekerjaan wali peserta didik</div>
                        </td>
                    </tr>

                    <!-- 48. Penghasilan Bulanan -->
                    <tr>
                        <td class="dp-td-num">48.</td>
                        <td class="dp-td-label">Penghasilan Bulanan</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-code-box"><?= !empty($data['nama_wali']) ? $kodePengWali : '&nbsp;&nbsp;' ?></span>
                            <span class="<?= !empty($data['nama_wali']) && $kodePengWali === '01' ? 'dp-opt-active' : 'dp-opt-muted' ?>">01) &lt; Rp. 500.000</span> &nbsp;
                            <span class="<?= !empty($data['nama_wali']) && $kodePengWali === '02' ? 'dp-opt-active' : 'dp-opt-muted' ?>">02) Rp. 500.000-Rp.999.999</span> &nbsp;
                            <span class="<?= !empty($data['nama_wali']) && $kodePengWali === '03' ? 'dp-opt-active' : 'dp-opt-muted' ?>">03) Rp. 1.000.000-Rp.1.999.999</span> &nbsp;
                            <span class="<?= !empty($data['nama_wali']) && $kodePengWali === '04' ? 'dp-opt-active' : 'dp-opt-muted' ?>">04) Rp.2.000.000-Rp.4.999.999</span> &nbsp;
                            <span class="<?= !empty($data['nama_wali']) && $kodePengWali === '05' ? 'dp-opt-active' : 'dp-opt-muted' ?>">05) Rp.5.000.000-Rp.20.000.000</span> &nbsp;
                            <span class="<?= !empty($data['nama_wali']) && $kodePengWali === '06' ? 'dp-opt-active' : 'dp-opt-muted' ?>">06) &gt; Rp.20.000.000</span> &nbsp;
                            <span class="<?= !empty($data['nama_wali']) && $kodePengWali === '07' ? 'dp-opt-active' : 'dp-opt-muted' ?>">07) Tidak Berpenghasilan</span>
                            <div class="dp-desc">Penghasilan Bulanan wali peserta didik</div>
                        </td>
                    </tr>
                </table>

                <div class="dp-footer-page">Halaman 2</div>
            </div>


            <!-- ═══════════════════════════════════════════════════════════════ -->
            <!-- HALAMAN 3 : KONTAK, PERIODIK, PRESTASI, & RINCIAN PENDAFTARAN  -->
            <!-- ═══════════════════════════════════════════════════════════════ -->
            <div class="dapodik-page" id="dapodik-hal-3">
                <table class="dp-table">
                    <!-- SECTION: KONTAK -->
                    <tr>
                        <td colspan="4" style="padding: 0;">
                            <div class="dp-section-bar">KONTAK</div>
                        </td>
                    </tr>

                    <!-- 49. Telepon Rumah -->
                    <tr>
                        <td class="dp-td-num">49.</td>
                        <td class="dp-td-label">Nomor telepon Rumah</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-val-direct" style="min-width: 140px; font-family: monospace;"><?= !empty($telpRumah) && $telpRumah !== '-' ? e($telpRumah) : '&nbsp;' ?></span>
                            <div class="dp-desc">Diisi nomor telepon rumah (milik pribadi, orang tua, atau wali) tanpa tanda baca</div>
                        </td>
                    </tr>

                    <!-- 50. Nomor HP -->
                    <tr>
                        <td class="dp-td-num">50.</td>
                        <td class="dp-td-label">Nomor Hp</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-val-direct" style="min-width: 160px; font-family: monospace;"><?= !empty($noHp) && $noHp !== '-' ? e($noHp) : '&nbsp;' ?></span>
                            <div class="dp-desc">Diisi nomor telepon selular (milik pribadi, orang tua, atau wali) tanpa tanda baca</div>
                        </td>
                    </tr>

                    <!-- 51. Email -->
                    <tr>
                        <td class="dp-td-num">51.</td>
                        <td class="dp-td-label">Email</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-val-direct full"><?= !empty($email) && $email !== '-' ? e($email) : '&nbsp;' ?></span>
                            <div class="dp-desc">Diisi alamat surat elektronik (surel) peserta didik yang dapat dihubungi (milik pribadi, orang tua, atau wali)</div>
                        </td>
                    </tr>

                    <!-- DIVIDER SEBELUM DATA RINCIAN -->
                    <tr>
                        <td colspan="4" style="padding: 2px 0 1px 0;">
                            <div style="border-bottom: 1px solid #333333; width: 100%;"></div>
                        </td>
                    </tr>

                    <!-- SUB BANNER: DATA RINCIAN PESERTA DIDIK -->
                    <tr>
                        <td colspan="4" style="padding: 0;">
                            <div class="dp-sub-section-bar">DATA RINCIAN PESERTA DIDIK</div>
                        </td>
                    </tr>

                    <!-- SECTION: DATA PERIODIK -->
                    <tr>
                        <td colspan="4" style="padding: 0;">
                            <div class="dp-section-bar">DATA PERIODIK</div>
                        </td>
                    </tr>

                    <!-- 1. Tinggi Badan -->
                    <tr>
                        <td class="dp-td-num">1.</td>
                        <td class="dp-td-label">Tinggi Badan</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-val-direct" style="min-width: 45px; text-align: center;"><?= !empty($tinggi) && $tinggi !== '-' ? e($tinggi) : '&nbsp;&nbsp;' ?></span> cm
                            <span class="dp-desc" style="display: inline; margin-left: 15px;">Tinggi badan peserta didik dalam satuan centimeter</span>
                        </td>
                    </tr>

                    <!-- 2. Berat Badan -->
                    <tr>
                        <td class="dp-td-num">2.</td>
                        <td class="dp-td-label">Berat Badan</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-val-direct" style="min-width: 45px; text-align: center;"><?= !empty($berat) && $berat !== '-' ? e($berat) : '&nbsp;&nbsp;' ?></span> kg
                            <span class="dp-desc" style="display: inline; margin-left: 15px;">Berat badan peserta didik dalam satuan kilogram</span>
                        </td>
                    </tr>

                    <!-- 3. Lingkar Kepala -->
                    <tr>
                        <td class="dp-td-num">3.</td>
                        <td class="dp-td-label">Lingkar Kepala</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-val-direct" style="min-width: 45px; text-align: center;"><?= !empty($lingkarKepala) && $lingkarKepala !== '-' ? e($lingkarKepala) : '&nbsp;&nbsp;' ?></span> cm
                            <span class="dp-desc" style="display: inline; margin-left: 15px;">Lingkar kepala peserta didik dalam satuan centimeter</span>
                        </td>
                    </tr>

                    <!-- 4. Jarak ke Sekolah -->
                    <tr>
                        <td class="dp-td-num">4.</td>
                        <td class="dp-td-label">Jarak tempat tinggal ke Sekolah</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-radio"><span class="dp-radio-circle checked"></span> Kurang dari 1 km</span>
                            <span class="dp-radio"><span class="dp-radio-circle"></span> lebih dari 1 km</span>
                            <span class="dp-desc" style="display: inline; margin-left: 10px;">Jarak rumah peserta didik ke sekolah</span>
                        </td>
                    </tr>

                    <!-- 5. Jarak KM -->
                    <tr>
                        <td class="dp-td-num">5.</td>
                        <td class="dp-td-label">Sebutkan (dalam kilometer)</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-val-direct" style="min-width: 45px; text-align: center;">&nbsp;&nbsp;</span> km
                            <div class="dp-desc">Apabila jarak rumah peserta didik ke sekolah lebih dari 1 km, isikan dengan angka jarak yang sebenarnya pada kolom ini dalam satuan kilometer.</div>
                        </td>
                    </tr>

                    <!-- 6. Waktu Tempuh -->
                    <tr>
                        <td class="dp-td-num">6.</td>
                        <td class="dp-td-label">Waktu tempuh ke sekolah (Jam/mnt)</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-val-direct" style="min-width: 35px; text-align: center;">0</span> jam &nbsp;
                            <span class="dp-val-direct" style="min-width: 35px; text-align: center;">15</span> Menit
                            <div class="dp-desc">Lama tempuh peserta didik ke sekolah. Kolom kiri adalah jam, kolom kanan adalah menit.</div>
                        </td>
                    </tr>

                    <!-- 7. Jumlah Saudara Kandung -->
                    <tr>
                        <td class="dp-td-num">7.</td>
                        <td class="dp-td-label">Jumlah Saudara kandung</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-code-box"><?= str_pad((string)$jumlahSaudara, 2, '0', STR_PAD_LEFT) ?></span>
                            <div class="dp-desc">Jumlah saudara kandung yang dimiliki peserta didik (tanpa menyertakan peserta didik). Isikan 0 apabila anak tunggal.</div>
                        </td>
                    </tr>

                    <!-- SECTION: REGISTRASI PESERTA DIDIK -->
                    <tr>
                        <td colspan="4" style="padding: 0;">
                            <div class="dp-section-bar">REGISTRASI PESERTA DIDIK</div>
                        </td>
                    </tr>

                    <!-- 1. Kompetensi Keahlian -->
                    <tr>
                        <td class="dp-td-num">1.</td>
                        <td class="dp-td-label">Kompetensi Keahlian</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-val-direct full">&nbsp;</span>
                            <div class="dp-desc">Kompetensi keahlian yang dipilih oleh peserta didik saat diterima di sekolah ini (khusus SMA/SMK)</div>
                        </td>
                    </tr>

                    <!-- 2. Jenis Pendaftaran -->
                    <tr>
                        <td class="dp-td-num">2.</td>
                        <td class="dp-td-label">Jenis pendaftaran</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-code-box"><?= $kodeJenisDaftar ?></span>
                            <span class="<?= $kodeJenisDaftar === '01' ? 'dp-opt-active' : 'dp-opt-muted' ?>">01) Siswa Baru</span> &nbsp;&nbsp;&nbsp;&nbsp;
                            <span class="<?= $kodeJenisDaftar === '02' ? 'dp-opt-active' : 'dp-opt-muted' ?>">02) Pindahan</span> &nbsp;&nbsp;&nbsp;&nbsp;
                            <span class="<?= $kodeJenisDaftar === '03' ? 'dp-opt-active' : 'dp-opt-muted' ?>">03) Kembali Bersekolah</span>
                            <div class="dp-desc">Status peserta didik saat pertama kali diterima di sekolah ini</div>
                        </td>
                    </tr>

                    <!-- 3. NIS / Nomor Induk PD -->
                    <tr>
                        <td class="dp-td-num">3.</td>
                        <td class="dp-td-label">NIS / Nomor Induk PD</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-val-direct" style="min-width: 140px; font-family: monospace;"><?= !empty($nis) && $nis !== '-' ? e($nis) : '&nbsp;' ?></span>
                            <div class="dp-desc">Nomor induk peserta didik sesuai yang tercantum pada buku induk</div>
                        </td>
                    </tr>

                    <!-- 4. Tanggal Masuk Sekolah -->
                    <tr>
                        <td class="dp-td-num">4.</td>
                        <td class="dp-td-label">Tanggal masuk sekolah</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-code-box"><?= $tglFormulirD ?></span> / <span class="dp-code-box"><?= $tglFormulirM ?></span> / <span class="dp-code-box" style="min-width: 40px;"><?= $tglFormulirY ?></span>
                            <div class="dp-desc">Tanggal pertama kali peserta didik diterima di sekolah ini</div>
                        </td>
                    </tr>

                    <!-- 5. Sekolah Asal -->
                    <tr>
                        <td class="dp-td-num">5.</td>
                        <td class="dp-td-label">Sekolah asal</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-val-direct full"><?= !empty($sekolahAsal) && $sekolahAsal !== '-' ? e($sekolahAsal) : '&nbsp;' ?></span>
                            <div class="dp-desc">Nama sekolah peserta didik sebelumnya (TK/PAUD, SD, atau SMP asal)</div>
                        </td>
                    </tr>

                    <!-- 6. Nomor Peserta UN -->
                    <tr>
                        <td class="dp-td-num">6.</td>
                        <td class="dp-td-label">Nomor Peserta UN SMP/MTs</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-val-direct full">&nbsp;</span>
                            <div class="dp-desc">Nomor peserta Ujian adalah 20 Digit yang tertera dalam SKHUN (Format Baku: 2-12-02-01-001-002-7), diisi bagi peserta didik jenjang SMP/SMA</div>
                        </td>
                    </tr>

                    <!-- 7. No Seri Ijazah -->
                    <tr>
                        <td class="dp-td-num">7.</td>
                        <td class="dp-td-label">No. Seri Ijazah Sebelumnya</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-val-direct full">&nbsp;</span>
                            <div class="dp-desc">Nomor seri Ijazah peserta didik pada jenjang sebelumnya</div>
                        </td>
                    </tr>

                    <!-- 8. No SKHUN -->
                    <tr>
                        <td class="dp-td-num">8.</td>
                        <td class="dp-td-label">No. SKHUN Sebelumnya</td>
                        <td class="dp-td-colon">:</td>
                        <td class="dp-td-val">
                            <span class="dp-val-direct full">&nbsp;</span>
                            <div class="dp-desc">Nomor seri SKHUN/SHUN peserta didik pada jenjang sebelumnya (jika memiliki)</div>
                        </td>
                    </tr>
                </table>

                <div class="dp-footer-page">Halaman 3</div>
            </div>

        </div>
        <?php
    }
}
