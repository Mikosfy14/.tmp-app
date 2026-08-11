<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <script>
        (function() {
            const savedTheme = LocalStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
        })();
    </script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Dashboard') ?> - .tmp Project Manager</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        body,
        #sidebar,
        .sidebar-wrapper,
        .sidebar-link,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        span,
        p,
        a {
            font-family: 'Nunito', sans-serif !important;
        }

        .sidebar-link {
            display: flex !important;
            align-items: center !important;
        }

        .sidebar-link i {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            margin-right: 0.75rem !important;
        }

        [data-bs-theme="dark"] body {
            background-color: #151521 !important;
            color: #a6a8b8 !important;
        }

        [data-bs-theme="dark"] .sidebar-wrapper {
            background-color: #1e1e2d !important;
            border-right: 1px solid #2b2b40 !important;
        }

        [data-bs-theme="dark"] .sidebar-wrapper .menu .sidebar-link {
            color: #a6a8b8 !important;
        }

        [data-bs-theme="dark"] .sidebar-wrapper .menu .sidebar-link:hover {
            background-color: #2b2b40 !important;
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .sidebar-wrapper .menu .sidebar-title {
            color: #565674 !important;
        }

        [data-bs-theme="dark"] .card {
            background-color: #1e1e2d !important;
            color: #ffffff !important;
            border: 1px solid #2b2b40 !important;
        }

        [data-bs-theme="dark"] .navbar-fixed {
            background-color: #1e1e2d !important;
        }
    </style>
</head>

<body>
    <div id="app">
        <?= $this->include('layouts/sidebar') ?>

        <div id="main" class='layout-navbar'>
            <?= $this->include('layouts/navbar') ?>

            <div id="main-content" class="pt-0">
                <div class="page-heading">
                    <?= $this->renderSection('content') ?>
                </div>

                <?= $this->include('layouts/footer') ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/main.js') ?>"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleDark = document.getElementById('toggle-dark');
            const savedTheme = localStorage.getItem('theme') || 'light';

            if (toggleDark) {
                if (savedTheme === 'dark') {
                    toggleDark.checked = true;
                    document.documentElement.setAttribute('data-bs-theme', 'dark');
                    document.body.classList.add('theme-dark');
                } else {
                    toggleDark.checked = false;
                    document.documentElement.setAttribute('data-bs-theme', 'light');
                    document.body.classList.remove('theme-dark');
                }

                toggleDark.addEventListener('change', function() {
                    if (this.checked) {
                        localStorage.setItem('theme', 'dark');
                        document.documentElement.setAttribute('data-bs-theme', 'dark');
                        document.body.classList.add('theme-dark');
                    } else {
                        localStorage.setItem('theme', 'light');
                        document.documentElement.setAttribute('data-bs-theme', 'light');
                        document.body.classList.remove('theme-dark');
                    }
                });
            }
        });
    </script>
</body>

</html>