<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Tautan Tidak Valid') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #f8fafc;
            padding: 24px;
        }
        .card {
            background: rgba(30, 41, 59, 0.85);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 48px 36px;
            max-width: 500px;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .icon-box {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(239, 68, 68, 0.15);
            border: 2px solid rgba(239, 68, 68, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            color: #ef4444;
            font-size: 32px;
        }
        h1 { font-size: 22px; font-weight: 700; margin-bottom: 12px; color: #fff; }
        p { font-size: 14px; color: #94a3b8; line-height: 1.6; margin-bottom: 28px; }
        .help-box {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 14px;
            padding: 16px;
            margin-bottom: 24px;
            font-size: 13px;
            color: #cbd5e1;
            text-align: left;
        }
        .help-box strong { color: #38bdf8; display: block; margin-bottom: 4px; }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 12px;
            background: #0284c7;
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all .2s;
        }
        .btn:hover { background: #0369a1; transform: translateY(-1px); }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-box">
            <i class="fa-solid fa-link-slash"></i>
        </div>
        <h1>Tautan Pengisian Tidak Valid</h1>
        <p>Mohon maaf, tautan pengisian formulir biodata lengkap yang Anda gunakan tidak ditemukan atau sudah kadaluarsa.</p>
        
        <div class="help-box">
            <strong><i class="fa-solid fa-circle-question"></i> Butuh Bantuan?</strong>
            Silakan hubungi Panitia SPMB SIT Bina Insan Palu melalui WhatsApp panitia pendaftaran untuk mendapatkan tautan formulir terbaru.
        </div>

        <a href="<?= htmlspecialchars(url('login')) ?>" class="btn">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda
        </a>
    </div>
</body>
</html>
