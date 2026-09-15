<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="<?= base_url('assets/images/logo/tmp_logo.png') ?>" type="image/png">
    <title>403 - Akses Ditolak | .tmp Team Dashboard</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            min-height: 100vh;
            background-color: #ebf3ff;
            color: #25396f;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .error-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
            padding: 3rem 2rem;
            max-width: 580px;
            width: 100%;
            text-align: center;
            border: 1px solid rgba(226, 232, 240, 0.8);
        }

        .img-error {
            max-width: 320px;
            width: 100%;
            height: auto;
            object-fit: contain;
            margin-bottom: 1.75rem;
        }

        .error-code-badge {
            display: inline-block;
            font-size: 0.85rem;
            font-weight: 700;
            padding: 4px 14px;
            border-radius: 50px;
            background-color: #fef2f2;
            color: #dc2626;
            margin-bottom: 1rem;
            letter-spacing: 0.5px;
        }

        .error-title {
            font-size: 1.65rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 0.75rem;
            line-height: 1.3;
        }

        .error-message {
            font-size: 1rem;
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .btn-cta {
            background-color: #435ebe;
            color: #ffffff;
            font-weight: 600;
            font-size: 0.95rem;
            padding: 12px 28px;
            border-radius: 10px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease-in-out;
            box-shadow: 0 4px 12px rgba(67, 94, 190, 0.25);
            border: none;
            cursor: pointer;
        }

        .btn-cta:hover {
            background-color: #354da8;
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(67, 94, 190, 0.35);
        }

        .brand-footer {
            margin-top: 2rem;
            font-size: 0.82rem;
            color: #94a3b8;
        }
    </style>
</head>

<body>
    <div class="error-card">
        <img src="<?= base_url('assets/images/error/image_error403.png') ?>" alt="Error 403 - Forbidden" class="img-error">
    
        <h1 class="error-title">Akses Ditolak</h1>
        
        <p class="error-message">
            <?= esc($message ?? 'Anda tidak memiliki akses menuju halaman ini. Silahkan kembali ke halaman sebelumnya') ?>
        </p>
        
        <button type="button" onclick="handleGoBack()" class="btn-cta">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Kembali ke halaman sebelumnya</span>
        </button>

    </div>

    <script>
        function handleGoBack() {
            if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = "<?= base_url('/dashboard') ?>";
            }
        }
    </script>
</body>

</html>
